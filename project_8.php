<?php 
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

        http_response_code(200);

        exit();
    
    }

    if ($_SERVER['REQUEST_METHOD'] === $_REQUEST) {

        http_response_code(405);

        echo json_encode([

            'status' => 'error',

            'message' => 'Method not allowed. Only POST requests are accepted.',

            'code' => 405

        ]);

        exit();

    }

    try {

    // Get POST data

        

        $input = json_decode(file_get_contents('php://input'), true);

        // If JSON input is empty, try regular POST data

        if (empty($input)) {

            $input = $_REQUEST;

        }

        // Validate required parameters

        $required_fields = ['number', 'msgdata', 'shortcode'];

        $missing_fields = [];

        foreach ($required_fields as $field) {

            if (!isset($input[$field]) || empty(trim($input[$field]))) {

                $missing_fields[] = $field;

            }

        }

        if (!empty($missing_fields)) {

            http_response_code(400);

            echo json_encode([

                'status' => 'error',

                'message' => 'Missing required fields: ' . implode(', ', $missing_fields),

                'code' => 400,

                'missing_fields' => $missing_fields

            ]);

            exit();

        }

        // Sanitize input data

        $number = strtolower(trim($input['number']));

        $msg_data = strtolower(trim($input['msgdata']));

        $short_code = strtolower(trim($input['shortcode']));

        // Process the data (you can add your business logic here)
        $MTN = ['0803', '0816', '0903', '0810', '0806', '0703', '0706', '0813', '0814', '0906', '0815'];
        $GLO = ['0805', '0905', '0807', '0811', '0705', '0815'];
        $NINE_MOBILE = ['0908', '0818' , '0809' , '0817' , '0909', '0908', '0818', '0809', '0817'];
        $AIRTEL = ['0907', '0708', '0802', '0902', '0812', '0808', '0701' ];


        //An array containting all the networks.
        $NETWORKS = [$MTN, $GLO, $NINE_MOBILE, $AIRTEL];
        $NETWORK = [
            'mtn' => $MTN,
            'glo' => $GLO,
            'nine-mobile' => $NINE_MOBILE,
            'airtel' => $AIRTEL 
        ];

        $VALID_SHORT_CODES = ['0','111', '222', '333', '444'];
        $SHORT_CODES_W_NAMES = [
            'peace' => '111',
            'chinasa' => '222',
            'bukunmi' => '333',
            'tomiwa' => '444'

        ];

        $responder_name = "";

        if(array_search($short_code, $VALID_SHORT_CODES) != false && is_numeric($short_code)){
            $responder_name = strtolower(array_search($short_code, $SHORT_CODES_W_NAMES));
        }else{
            http_response_code(response_code:200);
            echo json_encode([

                'status' => 'success',

                'message' => 'The Short Code entered is Invalid or Unavailable',

                'code' => 200,

            ]);

            die;

        }


        $phone_valid = false;
        $first_4_of_number = substr($number, 0, 4);
        $network = "";
        for($i = 0; $i < count($NETWORKS); $i++){
            for($j = 0; $j < count($NETWORKS[$i]); $j++){
                if($NETWORKS[$i][$j] == $first_4_of_number){
                    $network = array_search($NETWORKS[$i], $NETWORK);
                    $phone_valid = true;
                }
            }

        }

        if (is_numeric($number) == false || strlen($number) < 11){
            $phone_valid = false;
        }
        if(!$phone_valid){
            http_response_code(response_code:200);

            echo json_encode([

                'status' => 'success',

                'message' => 'The Phone number entered is incorrect.',

                'code' => 200,
                

            ]);

            die;

        }
        $date_time = date('Y-m-d H:i:s');
        $hour = date('H');
        $time_period = "";
        switch($hour){
            case '00':
            case '01':
            case '02':
            case '03':
            case '04':
            case '05':
            case '24':
            case '06':
            case '07':
            case '08':
            case '09':
            case '10':
            case '11':
                $time_period = "Morning";
                break;
            case '12':
            case '13':
            case '14':
            case '15':
            case '16':
                $time_period = "Afternoon";
                break;
            case '17':
            case '18':
            case '19':
            case '20':
                $time_period = "Evening";
                break;
            case '21':
            case '22':
            case '23':
                $time_period = 'Night';
                break;
            
        }

        $services = [
            'food' => "$responder_name's food:\n1. Jollof Rice and Chicken\n2. Spagetthi with meatballs\n3. Amala and Ewedun\n4. Plantain and egg\n5. Yam and Egg",
                    
            'drinks' => "$responder_name's Drinks\n1. Coke\n2. Pepsi\n3. Banana Smoothie\n4. Sprite\n5. Mirinda",
                        
            "network" => $network,
            "date" => 'Good' . ' ' . $time_period . ' the date and time is ' . $date_time,
            
            "self" => "I am fine, thanks for asking"
            
        ];

        $msg_reg_ex = "/{$responder_name}/i";
        $service_bool = false;
        foreach($services as $key => $value){
            $service_reg_ex = "/{$key}/i";
            if(preg_match($service_reg_ex, $msg_data)){
                $service_bool = true;
            }
        }

        $pos_msg = preg_match($msg_reg_ex, $msg_data);

        $rendered_service = "";

        if ($pos_msg && $service_bool){
            foreach($services as $key => $value){
                $service_reg = "/{$key}/i";
                if(preg_match($service_reg, $msg_data)){
                    $rendered_service = $services[$key];
                    // $rendered_service = implode("\n", $rendered_service);
                    // $r_s = "";
                    // foreach($rendered_service as $key){
                    //     $r_s .= implode($key);
                    // }
                }
            }
            header('Content-Type: text/plain');

            echo "Hello it is $responder_name's services.\n$rendered_service";
            // http_response_code(response_code:200);
            // echo json_encode([
            //     'status' => "success",

            //     'message' => 'Hello it is ' . $responder_name . "'s services \n\n" .  $rendered_service

            // ], JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        }else if( $service_bool == false){
            // http_response_code();
            // json_encode([
            //     "status" => "success",

            //     "message" => "Invalid service choice"
            // ]);

            echo "Invalid service choice";



        }
        
        else {
            http_response_code(200);

            echo json_encode([

                'status' => 'success',

                'message' => "Contact" . ' ' . $responder_name . ' at ' . $SHORT_CODES_W_NAMES[$responder_name],

                'code' => 200,

                // exit()


            ],  JSON_PRETTY_PRINT);


        }

    } catch (Exception $e) {

        http_response_code(500);

        echo json_encode([

            'status' => 'error',

            'message' => 'Internal server error: ' . $e->getMessage(),

            'code' => 500,

            exit()

        ]);

    }
?>


