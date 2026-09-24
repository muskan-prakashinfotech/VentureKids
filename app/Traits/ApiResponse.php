<?php

namespace App\Traits;

trait ApiResponse {
    public function sendResponse( $message, $code, $data = NULL) {
        $response = [
            'code' => $code,
            'message' => $message
        ];
        if($code == 422){
            $response = [
                'code' => $code,
                'message' => $message
            ];
        }
        if( isset($data) ){
            $response['data'] = $data;
        }
        return response()->json( $response, $code );
    }

    public function sendPaginationResponse( $message, $data = NULL ,$notfound = NULL) {
        $response = [
            'code' => 200,
            // 'message' => trans("app.$message")
            'message' => (sizeof($data['data']) > 0 )?trans("app.$message"):$notfound,
        ];
        if (isset($data)) {
            $response['data']   = $data['data'];
            $response['links']  = $data['links'];
            $response['meta']   = $data['meta'];
        }
        return response()->json( $response, 200);
    }

    // FOR API
    // 422 = validation
    // 401 = unathorise user
    // 200 = success

    // FOR WEB
    // 422 = validation
    // 404 = common error
}
