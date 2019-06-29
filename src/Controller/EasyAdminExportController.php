<?php

namespace Kreyu\Bundle\EasyAdminExportBundle\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Sebastian Wróblewski <kontakt@swroblewski.pl>
 */
class EasyAdminExportController extends EasyAdminController
{
    use ExportControllerTrait;

    public static function getSubscribedServices(): array
    {
        return parent::getSubscribedServices() + [
            'translator' => TranslatorInterface::class,
        ];
    }
}
