<?php

class ApiController {

    // Retorna resposta JSON
    protected function response($data, $status = 200) {

        http_response_code($status);
        header('Content-Type: application/json');

        echo json_encode($data);
        exit;
    }

    // Retorna erro JSON
    protected function error($msg, $status = 400) {

        $this->response([
            'erro' => true,
            'mensagem' => $msg
        ], $status);
    }
}
