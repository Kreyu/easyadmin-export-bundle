<?php

namespace Kreyu\Bundle\EasyAdminExportBundle\Event;

/**
 * @author Sebastian Wróblewski <kontakt@swroblewski.pl>
 */
final class EasyAdminExportEvents
{
    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    public const PRE_EXPORT = 'kreyu_easy_admin_export.pre_export';

    /** @Event("Symfony\Component\EventDispatcher\GenericEvent") */
    public const POST_EXPORT_QUERY_BUILDER = 'kreyu_easy_admin_export.post_export_query_builder';
}
