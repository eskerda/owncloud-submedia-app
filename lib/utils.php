<?php
/**
 * ownCloud - Media Playlists
 *
 * @author Lluís Esquerda
 * @copyright 2012 Blue-Systems contact@blue-systems.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

/**
 * This class contains utils for SubMedia
 */
class OC_Submedia_Utils {

    public static function requestDupedParams($query){
        $query  = explode('&', $query);
        $params = array();

        foreach( $query as $param )
        {
          list($name, $value) = explode('=', $param);
          $params[urldecode($name)][] = urldecode($value);
        }
        return $params;
    }

    public static function fixBooleanKeys($data, $booleankeys, $true, $false, $clean_function = NULL){
        foreach($data as $key=>$value){
            if (is_array($value)){
                $value = self::fixBooleanKeys($value, $booleankeys, $true, $false, $clean_function);
            } else {
                if (in_array($key, $booleankeys)){
                    $value = $value == true?$true:$false;
                }
                if ($clean_function != NULL && is_string($value))
                $value = $clean_function($value); 
            }
            $data[$key] = $value;
        }
        return $data;
    }
}