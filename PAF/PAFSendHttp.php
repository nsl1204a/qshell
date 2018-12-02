<?

require_once 'PAF/PAFObject.php';
require_once 'PAF/PAFHttpClient.php';


class PAFSendHttp extends PAFObject {
 // Attributes
    /**
     *    Contiene el socket al que hacemos las peticiones.
     *
     *    @access private
     */
    var $socket = null;

    /**
     * Contiene el username con el que llamar al script si necesario.
     * @access private
     */
    var $user;
    
    /**
     * Contiene el password asociado al user.
     * @access private
     */
    var $passwd;
   
    /**
     * Contiene el objeto PAFHttpClient que instanciamos para enviar los datos
     * @access private
     */ 
    var $oHttpClient = null;
    
    /**
     * Contiene el script que se envia.
     * @access private
     */
    var $script = null;
    
    /**
     * Contiene los parámetros de envio
     * @access private
     */
    var $params = null;
    
    
    
    function PAFSendHttp($script, $host, $port, $use=false, $passwd=false, $params) {
        
        $this->PAFObject();
        
	$this->host=$host;
	$this->port=$port;        
        
        $this->socket= new PAFSocket($this->host, $this->port);
        
        $this->user=$user;
        $this->passwd=$passwd;
        $this->script=$script;
        $this->params=$params;
        
        $this->oHttpClient=new PAFHttpClient($this->socket, $this->user, $this->passwd);
        
    }
    
    function MandaFicheroPrueba(){
    	
    	$result=$this->oHttpClient->POSTFichero($this->script, $this->params, NULL,"multipart");
    	
    }
    
    function send() {
    	
    	
    	
	
	$bResult=false;
	$result=$this->oHttpClient->POST($this->script, $this->params, NULL,"multipart");
	
	if (PEAR::isError($result))
		{
			$bResult = false;
					
			return $bResult;
		}
		
	return $this->oHttpClient->getContent();
    }
    
    function Headers() {
    	
    	$result=$this->oHttpClient->GetHeaders();
    	return $result;
    }
     function Content() {
    	
    	$result=$this->oHttpClient->GetContent();
    	
    	return $result;
    }
}
?>