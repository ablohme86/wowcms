<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * @property CI_DB_query_builder $auth
 * @property                     $multiRealm
 */
class Realm_model extends CI_Model
{
    private $RealmStatus;

    /**
     * General_model constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->RealmStatus = null;
        $this->auth        = $this->load->database('auth', true);
		$this->auth_tbc        = $this->load->database('auth_tbc', true);
		$this->cms_db = $this->load->database('default',true);


    }

    /**
     * @return CI_DB_result
     */
    public function getRealms(): CI_DB_result
    {
        return $this->db->select('*')->get('realms');
    }

    /**
     * @param $id
     *
     * @return CI_DB_result
     */



	// added by: A. Blohme
	public function getServersRealmID($id)
	{
		// *FIXED?* A. Blohme: Why does this just return 0 on realmID?? $id is correct.. Fix tomorrow from here !!!
     	$result = $this->cms_db->select('realmID')->where('id', $id)->get('realms')->row('realmID');		
     	
     	return $result;
	}


    /**
     * @param $id
     *
     * @return mixed
     */
     
     // Added by; A. Blohme
     public function isTbc($keyid)
     {

     	$result = $this->cms_db->select('*')->where('id', $keyid)->get('realms')->row('expansion');     	
     	return $result;
     }
 
 public function expansion($keyid)
   {
 
      $result = $this->cms_db->select('*')->where('id', $keyid)->get('realms')->row('expansion');     	
      return $result;
   }
     
    public function getRealmPort($id)	// denne gir bare 'realmID' da denne henter direkte fra wow realmd datasen
    {
    	return $this->auth->select('port')->where('id', $id)->get('realmlist')->row('port');    
    }

    /**
     * @param        $MultiRealm
     * @param  bool  $status
     *
     * @return bool|null
     */
    public function RealmStatus($MultiRealm, bool $status = false): ?bool
    {
        $port = $this->getRealmPort($MultiRealm);

        if ($this->config->item('check_realm_local')) {
            $host = $this->realmGetHostnameLocal($MultiRealm);
        } else {
            $host = $this->realmGetHostname($MultiRealm);
        }

        if ($this->RealmStatus != null) {
            return $this->RealmStatus;
        } else {
            if (! $status) {
                $cachestatus = $this->cache->file->get('realmstatus_' . $MultiRealm);

                if ($cachestatus !== false) {
                    return ($cachestatus == "online") ? true : false;
                }
            }

            if (fsockopen($host, $port, $errno, $errstr, 1.5)) {
                $this->RealmStatus = true;
            } else {
                $this->RealmStatus = false;
            }

            $this->cache->file->save('realmstatus_' . $MultiRealm, ($this->RealmStatus) ? "online" : "offline", 180);

            return $this->RealmStatus;
        }
    }

    /**
     * @param $id
     *
     * @return bool|object
     */

	 // Vi m� bytte ut dette f�le forvirrende navnet til "getRealmsCharDB_Data" eller noe i framtia, og s� lage andre som: getRealmWorldDB_Data osv osv
    public function getRealmConnectionData($id)
    {
//		printf("getRealConnectionData: %d", $id);
        $data = $this->getRealm($id)->row_array();

        if ($data > 0) {
            $ret_data = $this->realmConnection(
            
                $data['username'],
                $data['password'],
                $data['hostname'],
                $data['char_database'],
                $data['id']
                
            );
            		//print_r($ret_data);
            return $ret_data;
        }

        return false;
    }
    public function getRealm($actual_keyid,$sensitive = false): CI_DB_result	// 30.aug.24; la til $sensitive slik at vi ikke sender passord og hele sulamitten ved public henting av realm data i APIet (se realminfo_get i Api)
     {
        if ($sensitive == false)
        {
          $realm = $this->db->select('*')->where('id', $actual_keyid)->get('realms');          
        }
        else
        {
           $realm = $this->db->select('id,realmID,emulator,expansion')->where('id', $actual_keyid)->get('realms');          
    
        }
    //	printf("Trying to getRealm on id: %d", $actual_keyid);
         return $realm; 
     }
     
    public function getRealmInformation($id, $sensitive = false)
        {
    //		printf("getRealConnectionData: %d", $id);
            $data = $this->getRealm($id,$sensitive)->limit(1);
    
            if ($data > 0) {
               
                      //print_r($ret_data);
                return $data;
            }
    
            return false;
        }

    /**
     * @param $username
     * @param $password
     * @param $hostname
     * @param $database
     *
     * @return bool|object
     */
    public function realmConnection($username, $password, $hostname, $database, $realmid = 0) 
    {
        $dsn = 'mysqli://' .
               $username . ':' .
               $password . '@' .
               $hostname . '/' .
               $database . '?char_set=utf8&dbcollat=utf8_general_ci&cache_on=true&cachedir=/path/to/cache';

        return $this->load->database($dsn, true);
    }

    /**
     * @param $id
     *
     * @return mixed
     */
     
     // Edited, A. Blohme:
     
     // Replaced $id with the REAL 'realms' ID so we can check for expansion number
     // The turk had just used "realmID" when calling for these functions, which I found so confusing....
     // Now I can easily check if this is an expansion pack... Why didnt he just use 'id' from the start? 
     
    public function getRealmName($id)
    {
    	//printf("getRealmName id: %d<br>", $id);
    	if ($this->isTbc($id) == 0)
    	{
        	return $this->auth->select('name')->where('id', $this->getServersRealmID( $id))->get('realmlist')->row('name');
        }
        else
        {	
        	return $this->auth_tbc->select('name')->where('id', $this->getServersRealmID( $id))->get('realmlist')->row('name');
        }
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function realmGetHostname($id)
    {
    	if ($this->isTbc($id) == 0)
    	{
        	return $this->auth->select('address')->where('id', $id)->get('realmlist')->row('address');
        }
        else
        {
        	return $this->auth_tbc->select('address')->where('id', $id)->get('realmlist')->row('address');
        }
        

    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function realmGetHostnameLocal($id)
    {
    	if ($this->isTbc($id) == 0)
    	{
        	return $this->auth->select('localAddress')->where('id', $id)->get('realmlist')->row('localAddress');
        }
        else
        {
        	return $this->auth_tbc->select('localAddress')->where('id', $id)->get('realmlist')->row('localAddress');
        }
        

    }

    /**
     * @param $multiRealm
     * @param $id
     *
     * @return mixed
     */
    public function getGeneralCharactersSpecifyAcc($multiRealm, $id)	
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('*')->where('account', $id)->get('characters');
    }

    /**
     * @param $multiRealm
     * @param $name
     *
     * @return mixed
     */
    public function getGuidCharacterSpecifyName($multiRealm, $name)
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('guid')->where('name', $name)->get('characters')->row('guid');
    }

    /**
     * @param $id
     * @param $multiRealm
     *
     * @return mixed
     */
    public function getGeneralCharactersSpecifyGuid($id, $multiRealm)
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('*')->where('guid', $id)->get('characters');
    }

    /**
     * @param $multiRealm
     * @param $id
     *
     * @return mixed
     */
    public function getNameCharacterSpecifyGuid($multiRealm, $id)
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('name')->where('guid', $id)->get('characters')->row('name');
    }

    /**
     * @param $name
     * @param $multiRealm
     *
     * @return mixed
     */
    public function getCharNameAlreadyExist($name, $multiRealm)
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('name')->where('name', $name)->get('characters');
    }

    /**
     * @param $multiRealm
     * @param $id
     *
     * @return mixed
     */
    public function getCharExistGuid($multiRealm, $id)
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('guid')->where('guid', $id)->get('characters')->num_rows();
    }

    /**
     * @param $multiRealm
     * @param $id
     *
     * @return mixed
     */
    public function getAccountCharGuid($multiRealm, $id)
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('account')->where('guid', $id)->get('characters')->row('account');
    }

    /**
     * @param $id
     * @param $multiRealm
     *
     * @return mixed
     */
    public function getCharBanSpecifyGuid($id, $multiRealm)
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('guid')->where('guid', $id)->where('active', '1')->get('character_banned');
    }

    /**
     * @param $id
     * @param $multiRealm
     *
     * @return mixed
     */
    public function getCharName($id, $multiRealm)
    {
        $this->multiRealm = $multiRealm;

		
        return $this->multiRealm->select('name')->where('guid', $id)->get('characters')->row('name');
    }

    /**
     * @param $id
     * @param $multiRealm
     *
     * @return mixed
     */
    public function getCharHKs($id, $multiRealm)
    {
        $this->multiRealm = $multiRealm;
        
        $oldVanillaCP = $this->multiRealm->query("SHOW TABLES LIKE 'character_honor_cp'")->num_rows() > 0;
        
         if ($oldVanillaCP) {
            return $this->multiRealm->select('count(guid)')->where('guid', $id)->where('type', 1)->get('character_honor_cp')->row('count(guid)');

         }
         else
         {
          return $this->multiRealm->select('totalKills')->where('guid', $id)->get('characters')->row('totalKills');
         }
         
        echo "This is the HK table:";
        print_r($hkTable);
		/*if ($this->isTbc($realmid) == 0) 
		{
		}
		else
		{
			//print_r($multiRealm);
			echo "Totally kills " . $kills;
			return $kills;
		}
         */
         return 0;



    }

    /**
     * @param $id
     * @param $multiRealm
     *
     * @return mixed
     */
    public function getCharDKs($id, $multiRealm)
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('count(guid)')->where('guid', $id)->where('type', 2)->get('character_honor_cp')->row('count(guid)');
    }

    /**
     * @param $id
     * @param $multiRealm
     *
     * @return mixed
     */
    public function getCharLevel($id, $multiRealm)
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('level')->where('guid', $id)->get('characters')->row('level');
    }

    /**
     * @param $id
     * @param $multiRealm
     *
     * @return mixed
     */
    public function getCharActive($id, $multiRealm)
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('online')->where('guid', $id)->get('characters')->row('online');
    }

    /**
     * @param $id
     * @param $multiRealm
     *
     * @return mixed
     */
    public function getCharRace($id, $multiRealm)
    {
        $this->multiRealm = $multiRealm;
        //echo "dette er racen:";
        $svar = $this->multiRealm->select('race')->where('guid', $id)->get('characters')->row('race');
        print_r($svar);
        return $svar;
    }

    /**
     * @param $id
     * @param $multiRealm
     *
     * @return mixed
     */
    public function getCharClass($id, $multiRealm)
    {
        $this->multiRealm = $multiRealm;

        return $this->multiRealm->select('class')->where('guid', $id)->get('characters')->row('class');
    }

    /**
     * @param $multiRealm
     *
     * @return string
     */
    public function getCharactersOnlineAlliance($multiRealm): string
    {
        $this->multiRealm = $multiRealm;
        $races            = array('1', '3', '4', '7', '11', '22', '25');

        $qq = $this->multiRealm->select('guid')->where_in('race', $races)->where('online', '1')->get('characters');

        if ($qq->num_rows()) {
            return $qq->num_rows();
        } else {
            return '0';
        }
    }

    /**
     * @param $multiRealm
     *
     * @return string
     */
    public function getCharactersOnlineHorde($multiRealm): string
    {
        $this->multiRealm = $multiRealm;
        $races            = array('2', '5', '6', '8', '10', '9', '26');

        $qq = $this->multiRealm->select('guid')->where_in('race', $races)->where('online', '1')->get('characters');

        if ($qq->num_rows()) {
            return $qq->num_rows();
        } else {
            return '0';
        }
    }

    /**
     * @param $multiRealm
     *
     * @return string
     */
    public function getAllCharactersOnline($multiRealm): string
    {
        $this->multiRealm = $multiRealm;

        $qq = $this->multiRealm->select('online')->where('online', '1')->get('characters');

        if ($qq->num_rows()) {
            return $qq->num_rows();
        } else {
            return '0';
        }
    }

    /**
     * @param $multiRealm
     *
     * @return float|int
     */
    public function getPercentageOnlineAlliance($multiRealm)
    {
        $players    = $this->getCharactersOnlineAlliance($multiRealm);
        $total      = $this->getAllCharactersOnline($multiRealm);
        $percentage = ($players / $total) * 100;

        return $percentage;
    }

    /**
     * @param $multiRealm
     *
     * @return float|int
     */
    public function getPercentageOnlineHorde($multiRealm)
    {
        $players    = $this->getCharactersOnlineHorde($multiRealm);
        $total      = $this->getAllCharactersOnline($multiRealm);
        $percentage = ($players / $total) * 100;

        return $percentage;
    }

    /**
     * @param $MultiRealm
     * @param $id
     *
     * @return mixed
     */
    public function getInformationCharacter($MultiRealm, $id)
    {
        $this->multiRealm = $MultiRealm;

        return $this->multiRealm->select('*')->where('guid', $id)->get('characters');
    }

    /**
     * @param $soapUser
     * @param $soapPass
     * @param $soapHost
     * @param $soapPort
     * @param $soap_uri
     *
     * @return SoapClient|string
     * @throws SoapFault
     */
    public function connect($soapUser, $soapPass, $soapHost, $soapPort, $soap_uri)
    {
        $this->client = new SoapClient(
            null,
            array(
                "location"   => "http://" . $soapHost . ":" . $soapPort . "/",
                "uri"        => "urn:" . $soap_uri . "",
                "style"      => SOAP_RPC,
                "login"      => $soapUser,
                "password"   => $soapPass,
                "trace"      => 1,
                "exceptions" => 1
            )
        );

        if (is_soap_fault($this->client)) {
            return false;
        }

        return $this->client;
    }

    /**
     * @param $command
     * @param $soapUser
     * @param $soapPass
     * @param $soapHost
     * @param $soapPort
     * @param $soap_uri
     *
     * @return mixed
     * @throws SoapFault
     */
    public function commandSoap($command, $soapUser, $soapPass, $soapHost, $soapPort, $soap_uri)
    {
        $client = $this->connect($soapUser, $soapPass, $soapHost, $soapPort, $soap_uri);

        return $client->executeCommand(new SoapParam($command, "command"));
    }
}
