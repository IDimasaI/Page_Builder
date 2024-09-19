<?php 
class JsonAPI
{
    private function setJsonHeader($statusCode)
    {
        header('Content-Type: application/json, charset=utf-8');
        http_response_code($statusCode);
    }

    private function exitWithJsonResponse($status, $message, $statusCode)
    {
        $this->setJsonHeader($statusCode);
        if(isset($status) && isset($message)){
            echo json_encode(['status' => $status, 'message' => $message]);
        }
        exit;
    }

    public function success($message, $statusCode = 200)
    {
        $this->exitWithJsonResponse('success', $message, $statusCode);
    }

    public function fail($message, $statusCode = 200)
    {
        $this->exitWithJsonResponse('error', $message, $statusCode);
    }


    public function notFoundUser($message, $statusCode = 404)
    {
        $this->exitWithJsonResponse('Not_Found', $message, $statusCode);
    }

    public function foundUser($message, $statusCode = 200)
    {
        $this->exitWithJsonResponse('Found', $message, $statusCode);
    }

    public function nonContent($statusCode = 204)
    {
        $this->exitWithJsonResponse(null, null, $statusCode);
    }
}
?>