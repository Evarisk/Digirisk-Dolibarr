<?php
/* Copyright (C) 2021-2024 EVARISK <technique@evarisk.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 * or see https://www.gnu.org/
 */

/**
 * \file    core/modules/digiriskdolibarr/digiriskelement/accident/mod_accident_curtiss.php
 * \ingroup digiriskdolibarr
 * \brief   File that contains the numbering module rules curtiss
 */

// Load Saturne libraries
require_once __DIR__ . '/../../../../../../saturne/core/modules/saturne/modules_saturne.php';

/**
 * Class of file that contains the numbering module rules curtiss
 */
class mod_accident_curtiss extends CustomModeleNumRefSaturne
{
    /**
     * @var string Model name
     */
    public string $name = 'Curtiss';

    public function __construct()
    {
        self::setCustomValue('digiriskdolibarr', 'accident');
    }
}
