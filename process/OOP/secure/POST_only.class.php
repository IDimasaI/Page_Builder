<?php
class POST_only
{
    private string $url_redirect;

    public function __construct($url_redirect)
    {
        $this->url_redirect = $url_redirect;
        $this->check_POST();
    }

    public function check_POST()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: {$this->url_redirect}");
            exit;
        }
    }
}
?>