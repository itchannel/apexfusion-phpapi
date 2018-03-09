<?php
//Test


class Apex
{
	//Apex Test integration
	public $username = "";
	public $password = "";
	public $apexdevid = "";
	public $cookies = "";
	//Test return variables

	//$bob = apexstatus();

	//Returned Variables
	public $alarmstatus = "OFF";
	public $alarmmessage = "";
	public $apextemp = 0;
	public $apexph = 0;
	public $apexorp =0;
	public $apexcond = 0;
	public $apexlog = array();
	public $inputs = array();
	public $outputs = array();

Function apexstatus()
{
	//Fetch Data from Apexfusion.com and return class variables
	$apexdata = $this->fetchdata();
	$apexstatus = array();
	$this->apexlog = $this->fetchlogs();
	$this->inputs = $apexdata->inputs;
	$this->outputs = $apexdata->outputs;
	$this->devices = $this->parsedevices();
	$this->alarmstatus = $apexdata->alarm->status;
	if ($apexdata->alarm->status == "ON")
	{
		$this->alarmmessage = $apexdata->alarm->smnt;
	}
	foreach ($apexdata->inputs as $input)
	{
	switch ($input->did){
		case "base_Temp":
			$this->apextemp = $input->value ;
			break;
		case "base_pH":
			$this->apexph = $input->value;
			break;
		case "base_ORP":
			$this->apexorp = $input->value;
			break;
		case "base_Cond":
			$this->apexcond = $input->value;
			break;
	}
	}
	
	return True;

}

Function parsedevices()
{
	$key1 = array_column($this->inputs, 'name', 'did');                                 
        $key2 = array_column($this->outputs, 'name', 'did');
                                        
        $devices = array_merge($key1,$key2);
	return $devices;


}

Function fetchlogs()
{
 $today = date("Y-m-d");
 $url = "https://apexfusion.com/api/apex/$this->apexdevid/olog?page=1&per_page=50&total_pages=103&total_entries=2056&date=$today";
        //Grab status information from apex website
        $this->cookies = $this->ReadCookie();
        $request_headers = array();
        $request_headers[] = 'Cookie: '. $this->cookies;
        $request_headers[] = 'Accept: application/json, text/javascript, */*;';
        $request_headers[] = 'X-Requested-With: XMLHttpRequest';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this,'Responser'));
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $response_body = curl_exec($ch);
        if ($response_body == "User not authenticated")
        {
                echo "USER NOT SIGNED IN";
                $this->Login();
        }else
        {
                $data = json_decode($response_body);
		rsort($data[1]);
                //echo var_dump($data);
                return $data[1];

        }



}


Function fetchdata()
{
	$url = "https://apexfusion.com/api/apex/$this->apexdevid/status?";
	//Grab status information from apex website
	$this->cookies = $this->ReadCookie();
	$request_headers = array();
	$request_headers[] = 'Cookie: '. $this->cookies;
	$request_headers[] = 'Accept: application/json, text/javascript, */*;';
	$request_headers[] = 'X-Requested-With: XMLHttpRequest';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this,'Responser'));
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	$response_body = curl_exec($ch);
	if ($response_body == "User not authenticated")
	{
		echo "USER NOT SIGNED IN";
		$this->Login();
	}else
	{
		$data = json_decode($response_body);
        	//echo var_dump($data);
        	return $data;

	}

}

Function Login()
{
	//Get Request for CSRF
	


	$url = "https://apexfusion.com/login";
	$request_headers = array();
        $request_headers[] = 'Cookie: '. $this->cookies;
	$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this,'Responser'));
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $response_body = curl_exec($ch);

	$dom = new DomDocument();
	$dom->loadHTML($response_body);
	$tokens = $dom->getElementsByTagName("meta");
	for ($i = 0; $i < $tokens->length; $i++)
	{
    		$meta = $tokens->item($i);
    		if($meta->getAttribute('name') == 'csrf-token')
    			$token = $meta->getAttribute('content');

	}

	$url = "https://apexfusion.com/login";
	$request_headers = array();
        $request_headers[] = 'Origin: https://apexfusion.com';
	$request_headers[] = 'Accept: application/json, text/javascript, */*; q=0.01';
	$request_headers[] = 'Content-Type: application/json';
	$request_headers[] = 'Cookie: '. $this->cookies[1];
	$request_headers[] = 'csrf-token: ' . $token;
	$request_headers[] = 'Referer: https://apexfusion.com/login';
	$request_headers[] = 'X-Requested-With: XMLHttpRequest';
	$fields = array(
	'username' => $this->username,
	'password' => $this->password,
	'remember_me' => "false"
);

	$fields_string = json_encode($fields);
	echo $fields_string;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this,'Responser'));
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch,CURLOPT_POST, 1);
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	$response_body = curl_exec($ch);
        echo $response_body;
	$this->SaveCookie($this->cookies);

}

Function Responser($ch, $headerLine) {
    if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie) == 1)
        $this->cookies = $cookie;
    return strlen($headerLine); // Needed by curl
}

Function SaveCookie($cookies)
{
	//TODO - Move cookie file to non public location
	$myfile = fopen("011292.txt", "w") or die("Unable to open file!");

	$txt = $cookies[1];
	fwrite($myfile, $txt);
	fclose($myfile);
}

Function ReadCookie()
{
	$myfile = fopen("011292.txt", "r") or die("Unable to open file!");
	$cookies = fread($myfile,filesize("011292.txt"));
	fclose($myfile);
	return $cookies;

}
}

?>
