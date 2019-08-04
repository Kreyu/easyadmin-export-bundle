<?php

/*
 * This file is part of the EasyAdminExportBundle package.
 *
 * (c) Sebastian Wróblewski <kontakt@swroblewski.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kreyu\Bundle\EasyAdminExportBundle\Controller;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Exception;
use Kreyu\Bundle\EasyAdminExportBundle\Event\EasyAdminExportEvents;
use Kreyu\Bundle\EasyAdminExportBundle\Exception\UnsupportedFormatException;
use PhpOffice\PhpSpreadsheet\Exception as SpreadsheetException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use PhpOffice\PhpSpreadsheet\Writer\IWriter as WriterInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function in_array;
use function strtoupper;

/**
 * @author Sebastian Wróblewski <kontakt@swroblewski.pl>
 */
trait ExportControllerTrait
{
    /**
     * The method that is executed when the user performs an 'export' action on an entity.
     *
     * @return Response|StreamedResponse
     * @throws SpreadsheetException
     * @throws UnsupportedFormatException
     * @throws WriterException
     */
    public function exportAction()
    {
        $this->dispatch(EasyAdminExportEvents::PRE_EXPORT);

        $easyadmin = $this->request->attributes->get('easyadmin');
        $entityConfig = $easyadmin['entity'];

        $format = $this->request->query->get('format');
        $sortField = $this->request->query->get('sortField');
        $sortDirection = $this->request->query->get('sortDirection');

        if (!in_array($format, $entityConfig['export']['formats'])) {
            throw new UnsupportedFormatException(
                sprintf('Requested entity doesn\'t support the given "%s" file format', $format)
            );
        }

        if (null === $sortDirection || !in_array(strtoupper($sortDirection), ['ASC', 'DESC'])) {
            $sortDirection = 'DESC';
        }

        $queryBuilder = $this->createExportQueryBuilder(
            $entityConfig['class'],
            $sortDirection,
            $sortField,
            $entityConfig['list']['dql_filter']
        );

        $this->dispatch(EasyAdminExportEvents::POST_EXPORT_QUERY_BUILDER, [
            'query_builder' => $queryBuilder,
            'sort_field' => $sortField,
            'sort_direction' => $sortDirection,
        ]);

        $exportData = $this->createExportData($entityConfig, $queryBuilder);
        $spreadsheet = $this->createExportSpreadsheet($entityConfig, $exportData);
        $writer = $this->createExportWriter($spreadsheet, $format);

        return $this->createExportResponse($entityConfig, $writer, $format);
    }

    /**
     * Create Query Builder instance for all the records.
     *
     * @param  string $entityClass
     * @param  string $sortDirection
     * @param  string|null $sortField
     * @param  string|null $dqlFilter
     * @return QueryBuilder The Query Builder instance
     */
    protected function createExportQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        return $this->createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);
    }

    /**
     * Create an array of the ready-to-export entity data.
     *
     * @param  array $entityConfig
     * @param  QueryBuilder $queryBuilder
     * @return array Ready-to-export entity data
     */
    protected function createExportData($entityConfig, $queryBuilder)
    {
        /**
         * @var TranslatorInterface $translator
         */
        $translator = $this->get('translator');

        /**
         * @var PropertyAccessorInterface $accessor
         */
        $accessor = $this->get('easyadmin.property_accessor');

        $fields = $entityConfig['export']['fields'];
        $entities = $queryBuilder->getQuery()->getResult();

        // Create a temporary array to store the export data.
        $data = [];

        // Push the translated headers to the export data if this feature is enabled.
        if ($entityConfig['export']['use_headers']) {
            $headers = [];

            foreach ($fields as $field) {
                // If the property label is not defined in the configuration, use the property name.
                $headers[] = $translator->trans($field['label'] ?? ucfirst($field['property']));
            }

            $data[] = $headers;
        }

        // Process and push the entity data to the export data array.
        foreach ($entities as $entity) {
            $record = [];

            foreach ($fields as $field) {
                // Retrieve the entity property value using the property accessor.
                $value = $accessor->getValue($entity, $field['property']);

                // Call the transformer if given.
                if (array_key_exists('transformer', $field) && is_callable($field['transformer'])) {
                    $value = call_user_func($field['transformer'], $value, $field, $entityConfig);
                }

                // If property is type 'association' - its Doctrine Collection value has to be converted to string.
                if ($value instanceof Collection) {
                    $value = $value->map(function ($item) {
                        return (string) $item;
                    });

                    $value = implode(', ', $value->toArray());
                }

                $record[] = $value;
            }

            $data[] = $record;
        }

        return $data;
    }

    /**
     * Create an instance of the Spreadsheet.
     *
     * @param  array $entityConfig
     * @param  array $data
     * @return Spreadsheet The Spreadsheet instance
     * @throws SpreadsheetException
     */
    protected function createExportSpreadsheet($entityConfig, $data)
    {
        /**
         * @var PropertyAccessorInterface $accessor
         */
        $accessor = $this->get('easyadmin.property_accessor');

        $spreadsheet = new Spreadsheet();
        $properties = $spreadsheet->getProperties();

        // Set the spreadsheet metadata using the values defined in the configuration.
        foreach ($entityConfig['export']['metadata'] as $property => $value) {
            if (null !== $value) {
                $accessor->setValue($properties, $property, $value);
            }
        }

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($data);

        return $spreadsheet;
    }

    /**
     * Create an instance of the writer for the given format.
     *
     * @param  Spreadsheet $spreadsheet
     * @param  string $format
     * @return WriterInterface The writer for the given format
     * @throws WriterException
     */
    protected function createExportWriter($spreadsheet, $format)
    {
        return IOFactory::createWriter($spreadsheet, ucfirst(mb_strtolower($format)));
    }

    /**
     * Create an instance of the streamed response to return.
     *
     * @param  array $entityConfig
     * @param  WriterInterface $writer
     * @param  string $format
     * @return StreamedResponse
     * @throws Exception
     */
    protected function createExportResponse($entityConfig, $writer, $format)
    {
        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });

        $filename = $entityConfig['export']['filename'];

        if ($entityConfig['export']['timestamp']) {
            if ($timestampPrefix = $entityConfig['export']['timestamp_prefix']) {
                $filename .= $timestampPrefix;
            }

            if ($timestampFormat = $entityConfig['export']['timestamp_format']) {
                $filename .= (new DateTime)->format($timestampFormat);
            }
        }

        $response->headers->set('Content-Disposition', "attachment;filename=\"{$filename}.{$format}\"");
        $response->headers->set('Content-Type', $entityConfig['export']['headers']['content_type']);
        $response->headers->set('Cache-Control', $entityConfig['export']['headers']['cache_control']);

        return $response;
    }
}
