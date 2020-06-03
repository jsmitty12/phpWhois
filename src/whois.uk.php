<?php
/**
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2
 * @license
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 *
 * @link      http://phpwhois.pw
 * @copyright Copyright (C)1999,2005 easyDNS Technologies Inc. & Mark Jeftovic
 * @copyright Maintained by David Saez
 * @copyright Copyright (c) 2014 Dmitry Lukashin
 */

if (!defined('__UK_HANDLER__')) {
    define('__UK_HANDLER__', 1);
}

require_once 'whois.parser.php';

class uk_handler
{
    const ITEMS = [
        'owner.organization' => 'Registrant:',
        'owner.address'      => "Registrant's address:",
        'owner.type'         => 'Registrant type:',
        'domain.created'     => 'Registered on:',
        'domain.changed'     => 'Last updated:',
        'domain.expires'     => 'Expiry date:',
        'domain.nserver'     => 'Name servers:',
        'domain.sponsor'     => 'Registrar:',
        'domain.status'      => 'Registration status:',
        'domain.dnssec'      => 'DNSSEC:',
        ''                   => 'WHOIS lookup made at',
        'disclaimer'         => '--',
    ];

    /**
     * @param array  $data_str
     * @param string $query
     *
     * @return array
     */
    public function parse($data_str, $query)
    {
        $r             = [];
        $r['regrinfo'] = get_blocks($data_str['rawdata'], static::ITEMS);

        if (isset($r['regrinfo']['owner'])) {
            $r['regrinfo']['owner']['organization'] = $r['regrinfo']['owner']['organization'][0];
            $r['regrinfo']['domain']['sponsor']     = $r['regrinfo']['domain']['sponsor'][0];
            $r['regrinfo']['registered']            = 'yes';
        } elseif (strpos($query, '.co.uk') && isset($r['regrinfo']['domain']['status'][0])) {
            if ($r['regrinfo']['domain']['status'][0] == 'Registered until expiry date.') {
                $r['regrinfo']['registered']            = 'yes';
            }
        } else {
            if (strpos($data_str['rawdata'][1], 'Error for ')) {
                $r['regrinfo']['registered']       = 'yes';
                $r['regrinfo']['domain']['status'] = 'invalid';
            } else {
                $r['regrinfo']['registered'] = 'no';
            }
        }

        $r = format_dates($r, 'dmy');

        $r['regyinfo'] = [
            'referrer'  => 'http://www.nominet.org.uk',
            'registrar' => 'Nominet UK',
        ];

        if (!array_key_exists('rawdata', $r) && array_key_exists('rawdata', $data_str)) {
            $r['rawdata'] = $data_str['rawdata'];
        }

        return $r;
    }
}
