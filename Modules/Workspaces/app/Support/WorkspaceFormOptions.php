<?php

namespace Modules\Workspaces\Support;

class WorkspaceFormOptions
{
    /**
     * @return list<string>
     */
    public static function timezones(): array
    {
        return [
            'America/Mexico_City',
            'America/Bogota',
            'America/Lima',
            'America/Guayaquil',
            'America/Santiago',
            'America/Buenos_Aires',
            'America/Sao_Paulo',
            'UTC',
        ];
    }

    /**
     * @return list<string>
     */
    public static function industryCategories(): array
    {
        return [
            'Retail',
            'Salud',
            'Educación',
            'Tecnología',
            'Alimentos y bebidas',
            'Servicios profesionales',
            'Otro',
        ];
    }

    /**
     * @return list<string>
     */
    public static function regions(): array
    {
        return [
            'LATAM',
            'NA',
            'EU',
            'global',
        ];
    }
}
