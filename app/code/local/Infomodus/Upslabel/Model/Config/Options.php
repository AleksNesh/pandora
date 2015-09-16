<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Owner
 * Date: 06.02.12
 * Time: 16:17
 * To change this template use File | Settings | File Templates.
 */
class Infomodus_Upslabel_Model_Config_Options
{
    public function getProvinceCode($code)
    {
        $provinceCode = array(
            'Alabama' => 'AL',
            'Alaska' => 'AK',
            'American Samoa' => 'AS',
            'Arizona' => 'AZ',
            'Arkansas' => 'AR',
            'Armed Forces Africa' => 'AE',
            'Armed Forces Americas' => 'AA',
            'Armed Forces Canada' => 'AE',
            'Armed Forces Europe' => 'AE',
            'Armed Forces Middle East' => 'AE',
            'Armed Forces Pacific' => 'AP',
            'California' => 'CA',
            'Colorado' => 'CO',
            'Connecticut' => 'CT',
            'Delaware' => 'DE',
            'District of Columbia' => 'DC',
            'Federated States Of Micronesia' => 'FM',
            'Florida' => 'FL',
            'Georgia' => 'GA',
            'Guam' => 'GU',
            'Hawaii' => 'HI',
            'Idaho' => 'ID',
            'Illinois' => 'IL',
            'Indiana' => 'IN',
            'Iowa' => 'IA',
            'Kansas' => 'KS',
            'Kentucky' => 'KY',
            'Louisiana' => 'LA',
            'Maine' => 'ME',
            'Marshall Islands' => 'MH',
            'Maryland' => 'MD',
            'Massachusetts' => 'MA',
            'Michigan' => 'MI',
            'Minnesota' => 'MN',
            'Mississippi' => 'MS',
            'Missouri' => 'MO',
            'Montana' => 'MT',
            'Nebraska' => 'NE',
            'Nevada' => 'NV',
            'New Hampshire' => 'NH',
            'New Jersey' => 'NJ',
            'New Mexico' => 'NM',
            'New York' => 'NY',
            'North Carolina' => 'NC',
            'North Dakota' => 'ND',
            'Northern Mariana Islands' => 'MP',
            'Ohio' => 'OH',
            'Oklahoma' => 'OK',
            'Oregon' => 'OR',
            'Palau' => 'PW',
            'Pennsylvania' => 'PA',
            'Puerto Rico' => 'PR',
            'Rhode Island' => 'RI',
            'South Carolina' => 'SC',
            'South Dakota' => 'SD',
            'Tennessee' => 'TN',
            'Texas' => 'TX',
            'Utah' => 'UT',
            'Vermont' => 'VT',
            'Virgin Islands' => 'VI',
            'Virginia' => 'VA',
            'Washington' => 'WA',
            'West Virginia' => 'WV',
            'Wisconsin' => 'WI',
            'Wyoming' => 'WY',
            /* Canada */
            'Alberta' => 'AB',
            'British Columbia' => 'BC',
            'Manitoba' => 'MB',
            'New Brunswick' => 'NB',
            'Newfoundland and Labrador' => 'NL',
            'Northwest Territories' => 'NT',
            'Nova Scotia' => 'NS',
            'Nunavut' => 'NU',
            'Ontario' => 'ON',
            'Prince Edward Island' => 'PE',
            'Quebec' => 'QC',
            'Saskatchewan' => 'SK',
            'Yukon' => 'YT',
            '' => '',
        );
        if (array_key_exists($code, $provinceCode)) {
            return $provinceCode[$code];
        }
        else {
            return '';
        }
    }

    public function getUnitOfMeasurement(){
        $array = array(
            'IN' => 'Inches',
            'CM' => 'Centimeters',
            '00' => 'Metric Units of Measurement',
            '01' => 'English Units of Measurement',
        );
        return $array;
    }
}