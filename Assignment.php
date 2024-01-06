<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adchieve Code Assignment</title>
</head>
<body>

    <header>
        <h1>Adchieve Code Assignment</h1>
    </header>

    <main>
        <?php
            $apiKey = '9eb61b6aa98a57d4201f19b0253c92aa';
            $apiEndpoint = 'http://api.positionstack.com/v1/forward';
            $locationArray = array(
            'Adchieve HQ-Sint Janssingel 92, 5211 DA \'s-Hertogenbosch, The Netherlands',
            'Eastern Enterprise B.V.-Deldenerstraat 70, 7551AH Hengelo, The Netherlands', 
            'Eastern Enterprise-46/1 Office no 1 Ground Floor , Dada House , Inside dada silk mills compound, Udhana Main Rd, near Chhaydo Hospital, Surat, 394210, India',
            'Adchieve Rotterdam-Weena 505, 3013 AL Rotterdam, The Netherlands',
            'Sherlock Holmes-221B Baker St., London, United Kingdom',
            'The White House-1600 Pennsylvania Avenue, Washington, D.C., USA',
            'The Empire State Building-350 Fifth Avenue, New York City, NY 10118',
            'The Pope-Saint Martha House, 00120 Citta del Vaticano, Vatican City',
            'Neverland-5225 Figueroa Mountain Road, Los Olivos, Calif. 93441, USA'
            );

            $adchieveLatitude;
            $adchieveLongitude;
            
            //The final list which will be printed to the csv file 
            $printList = array();

            foreach($locationArray as $location)
            {
                $splitArray = explode("-", $location);
                $locationName = $splitArray[0];
                $locationAdress = $splitArray[1];

                $queryString = http_build_query([
                    'access_key' => $apiKey,
                    'query' => $locationAdress,
                    'output' => 'json',
                    'limit' => 1,
                ]);
  
                $ch = curl_init(sprintf('%s?%s',  $apiEndpoint, $queryString));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $json = curl_exec($ch);
                $response = curl_close($ch);

                if ($response === false) 
                {
                    echo 'Curl error: ' . curl_error($ch);
                }
                else 
                {
                    $data = json_decode($json, true);
                    $latitude = $data['data'][0]['latitude'];
                    $longitude = $data['data'][0]['longitude'];
                    
                    //save the Adchieve HQ Data to calculate with it later
                    if($locationName == 'Adchieve HQ')
                    {
                        $adchieveLatitude = $latitude;
                        $adchieveLongitude = $longitude;
                    }
                    else
                    {
                        $distance = calculateDistance($adchieveLatitude, $adchieveLongitude, $latitude, $longitude);
                        $printList[] = array('Name' => $locationName, 'Distance' => "$distance km", 'Address' => $locationAdress);
                    }
            }
        }

        usort($printList, 'sortDistance');

        function sortDistance($location1, $location2) {
            return $location1['Distance'] - $location2['Distance'];
        }

        //Calculation of two coördinates with the haversine-formule
        function calculateDistance($lat1, $lon1, $lat2, $lon2) {
            $R = 6371; // EarthRadius in kilometres
        
            $dLat = deg2rad($lat2 - $lat1);
            $dLon = deg2rad($lon2 - $lon1);
        
            $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
            $distance = $R * $c; // distance in kilometres
        
            return round($distance, 2);
        }

        $fields = array('Sortnumber', 'Distance', 'Name', 'Address');
        $filename = "Adchieve_Code_Assignment_Distance.csv";
        $csvFile = fopen($filename, "w");
        fputcsv($csvFile, $fields,",");  
        $sortnumber = 0;
        foreach($printList as $row)
        {
            $sortnumber++;
            fputcsv($csvFile, array($sortnumber, $row['Distance'], $row['Name'], $row['Address']),"," );
        }
        fclose($csvFile);
        echo "CSV file created in the source directory!!";
        ?>
    </main>
</body>
</html>


<?php 
//! Ik wilde api aanvraag eigenlijk als batch call gaan doen, maar dit is de error die ik uiteindelijk kreeg :(
//! Array ([error] => Array([code] => function_access_restricted [message] => Your current subscription plan does not support this API function))

// $batchArray = [
//     [
//         'query' => 'Sint Janssingel 92, 5211 DA \'s-Hertogenbosch, The Netherlands',
//     ],
//     [
//         'query' => 'Deldenerstraat 70, 7551AH Hengelo, The Netherlands',
//     ],
//     [
//         'query' => 'Weena 505, 3013 AL Rotterdam, The Netherlands',
//     ],
//     [
//         'query' => '221B Baker St., London, United Kingdom',
//     ],
// ];

// $batchRequests = [
//     'batch' => $batchArray,
// ];

// $chb = curl_init($apiEndpoint);
// curl_setopt($chb, CURLOPT_RETURNTRANSFER, true);
// curl_setopt($chb, CURLOPT_POST, true);
// curl_setopt($chb, CURLOPT_POSTFIELDS, json_encode($batchRequests));
// curl_setopt($chb, CURLOPT_HTTPHEADER, [
//     'Content-Type: application/json',
// ]);

// curl_setopt($chb, CURLOPT_URL, $apiEndpoint . '?access_key=' . $apiKey);

// $responseB = curl_exec($chb);

// if ($responseB === false) {
//     echo 'Curl error: ' . curl_error($chb);
// } else {
//     $data = json_decode($responseB, true);
//     print_r($data);
//     foreach ($data['data'] as $result) {
//         $name = $result[0]['name'];
//         $latitude = $result[0]['latitude'];
//         $longitude = $result[0]['longitude'];

//         echo "$name : Latitude = $latitude : Longitude = $longitude <br>";
//     }
// }
 
?>