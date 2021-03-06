<?php

namespace Uspdev;

class Boleto
{
    private $clienteSoap;
    public function __construct($user, $pass, $wsdl = false)
    {
	if (!$wsdl) {
           $wsdl = getcwd() . '/vendor/uspdev/boleto/src/wsdl/prod.wsdl';
        }

        require_once __DIR__ . '/../../../econea/nusoap/src/nusoap.php';
        $this->clienteSoap = new \nusoap_client($wsdl, 'wsdl');
        $erro = $this->clienteSoap->getError();
        if ($erro) {
	    print_r($erro); 
	    die();
        }
        $this->clienteSoap->setHeaders(array('username' => $user,'password' => $pass));
    }

    public function gerar($data)
    {
        $request = $this->clienteSoap->call('gerarBoleto', array('requisicao' => $data));

        if ($this->clienteSoap->fault) {
	    return $request["detail"]["WSException"];
	}
        else {
            return  $request['identificacao']['codigoIDBoleto'];
        }
    }

    public function situacao($codigoIDBoleto){
        $param = array('codigoIDBoleto'=>$codigoIDBoleto);
        return $this->clienteSoap->call('obterSituacao', array('identificacao'=>$param));
    }

    public function obter($codigoIDBoleto)
    {
        $param = array('codigoIDBoleto' => $codigoIDBoleto);
	      $request = $this->clienteSoap->call('obterBoleto', array('identificacao' => $param));
	      if ($this->clienteSoap->fault) print_r($request); 
	      if ($this->clienteSoap->getError())  print_r($request);

        //redirecionando os dados binarios do pdf para o browser 
        header('Content-type: application/pdf'); 
        header('Content-Disposition: attachment; filename="boleto.pdf"'); 
        echo base64_decode($request['boletoPDF']);
    }    
}

