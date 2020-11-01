<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class discordlinkCovid
{

    public function generateCovid ($nom = null, $prenom = null, $date_naissance = null, $lieu_naissance = null, $motif = null) {

        $adresse = config::bykey("info::address");
        $code_postal = config::bykey("info::postalCode");
        $ville = config::bykey("info::city");

        (empty($adresse)) ? $adresse = "pas d'adresse" : null;
        (empty($code_postal)) ? $code_postal = "12345" : null;
        (empty($ville)) ? $ville = "pas de Ville" : null;

        $nom = self::encodeTag($nom);
        $prenom = self::encodeTag($prenom);
        $date_naissance = self::encodeTag($date_naissance);
        $lieu_naissance= self::encodeTag($lieu_naissance);
        $adresse = self::encodeTag($adresse);
        $code_postal = self::encodeTag($code_postal);
        $ville = self::encodeTag($ville);
        $motif = self::encodeTag($motif);


        // Génération de l'url du QR Code
        //createAT=11%2F01%2F2020+18%3A20&datesortie=2020-11-10&heuresortie=20%3A30&

        $url = "https://attestation.les2t.fr/#prenom=$prenom&nom=$nom&date-de-naissance=$date_naissance&lieu-de-naissance=$lieu_naissance&adresse=$adresse&ville=$ville&code-postal=$code_postal&reason=$motif&autogenpdf";

        return $url;
    }

    function encodeTag($string) {
        $entities = array('+', 'é', 'è', 'à', 'ç', 'ù', '"', '%22', '/');
        $replacements = array('%20', '%C3%A9', '%C3%A8', '%C3%A0', '%C3%A7', '%C3%B9', '%C3%AB', '', '', '%2F');
        return str_replace($entities, $replacements, urlencode($string));
    }

}
