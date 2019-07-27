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
