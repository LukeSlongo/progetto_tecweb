<?php

use PHPUnit\Framework\TestCase;
use App\Models\UserModel;

class UserModelTest extends TestCase
{
    public function test_register_crea_utente_con_password_hashata_correttamente()
    {
        $username = "mario.rossi";
        $passwordInChiaro = "PasswordSegreta123!";
        $role = "student";

        $userModelMock = $this->getMockBuilder(UserModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['query']) 
            ->getMock();

        $userModelMock->expects($this->once())
            ->method('query')
            ->with(
                $this->equalTo("INSERT INTO `user` (username, password, role) VALUES (?, ?, ?)"),
                
                $this->callback(function ($params) use ($username, $passwordInChiaro, $role) {
                    
                    $isUsernameCorretto = ($params[0] === $username);
                    $isRuoloCorretto = ($params[2] === $role);
                    
                    $isPasswordHashata = password_verify($passwordInChiaro, $params[1]);

                    return $isUsernameCorretto && $isPasswordHashata && $isRuoloCorretto;
                })
            );

        $userModelMock->register($username, $passwordInChiaro, $role);
    }
}