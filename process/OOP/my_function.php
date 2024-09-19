<?php

namespace secure_DM;


class password_crypt
{
    private string $algo = 'sha256';
    private string $algo2 = 'sha512';
    private string $salt_shablon = 'ebgDZ:_X#BM-=2fi|S(N`0o8:2F8:ObE[XG)QG?@';
    public function send_crypt_pass($shablon, $secret_key)
    {
        $token = hash_hmac($this->algo2, $shablon, $secret_key);
        return $token;
    }

    public function receive_crypt_pass($shablon, $secret_key)
    {
        $token = hash_hmac($this->algo2, $shablon, $secret_key);
        return $token;
    }

    public function salt($login)
    {
        $salt = hash_hmac($this->algo, $this->salt_shablon, $login);
        return $salt;
    }
}