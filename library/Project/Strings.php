<?php

class Project_Strings
{

	/**
	 * Gerar nova Senha
	 * @desc Cria uma senha aleatória para o usuario.
	 * @author Emerson Carvalho <emerson.broga@gmail.com>
	 * @since 01/05/2011
	 * @param $lenth INT Quantidade de caracteres
	 * @param $level INT Nivel de senha, letras, numeros, caracteres especiais.
	 * @return String Senha Aleatoria.
	 */
    public static function generateNewPassword($length=6, $level=2)
    {
        list($usec, $sec) = explode(' ', microtime());
        srand((float) $sec + ((float) $usec * 100000));

        $validchars[1] = "0123456789abcdfghjkmnpqrstvwxyz";
        $validchars[2] = "0123456789abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $validchars[3] = "0123456789_!@#$%&*()-=+/abcdfghjkmnpqrstvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_!@#$%&*()-=+/";

        $password = "";
        $counter = 0;

        while($counter < $length) {
            $actChar = substr($validchars[$level], rand(0, strlen($validchars[$level]) - 1), 1);

            // All character must be different
            if(!strstr($password, $actChar))
            {
                $password .= $actChar;
                $counter++;
            }
        }

        return $password;
    }
    
	public static function excerpt($text, $chars = 100 )
	{	
		if( strlen($text) > $chars )
		{
			$text = $text . " ";
		    $text = strip_tags($text);
		    $text = substr($text,0,$chars);
		    $text = substr($text,0,strrpos($text,' '));
		    $text = $text . "...";
		}
	    return $text; 
	}
}