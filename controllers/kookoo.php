<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Kookoo extends CI_Controller {

	public function __construct(){
		parent:: __construct();
		
		//Load email library
		$this->load->library('Kookoo_response');
		$this->Kookoo_response = new Kookoo_response();
		$this->load->library('Kookoo_CollectDtmf');
		$this->Kookoo_CollectDtmf = new Kookoo_CollectDtmf();
		$this->load->library('session');
	}

	//default tickets page
	public function index() {
		$voiceDelay = 2;
		$setTimeOut= 6000;
		$this->Kookoo_response->setFiller("yes");
		if ($this->input->get('event')=="Disconnect" || $this->input->get('event')=="Hangup" ){
			exit;
		}
		else if($this->input->get('event')=="NewCall")
		{
			$this->session->set_userdata('cid',$this->input->get('cid'));
			$this->Kookoo_CollectDtmf->addPlayText("Welcome to the Orchid Hospitals Helpdesk.  ",$voiceDelay);
			$this->Kookoo_CollectDtmf->addPlayText("Please select department that you want to raise issue in.   ",$voiceDelay);
			$this->Kookoo_CollectDtmf->addPlayText("1. for Housekeeping",$voiceDelay);
			$this->Kookoo_CollectDtmf->setMaxDigits('1');
			$this->Kookoo_CollectDtmf->setTimeOut($setTimeOut);
			$this->Kookoo_response->addCollectDtmf($this->Kookoo_CollectDtmf);
			$this->session->set_userdata('state',"page1");
		}
		else if ($this->input->get('event') == 'GotDTMF' && $this->session->userdata('state')== 'page1' )
		{
			$data = $this->input->get('data');
			$this->Kookoo_CollectDtmf->setTimeOut($setTimeOut);
			if(is_numeric($data) && $data == 1){
					$infoData = array('category' =>(int)$data, 'phone'=> $this->input->get('cid'));
				//$resultData = createTicket($infoData);
				if($resultData['result'] = 'success')
					$this->Kookoo_response->addPlayText('Issue has been raised successfully.',$voiceDelay);
				
				$this->Kookoo_response->addPlayText('Thank you for calling, have a nice day',$voiceDelay);
				$this->Kookoo_response->addHangup();
			}else{
				$this->Kookoo_CollectDtmf->addPlayText('Invalid input, Press 1 for housekeeping.',$voiceDelay);
				$this->Kookoo_CollectDtmf->setMaxDigits('1');
				$this->Kookoo_response->addCollectDtmf($this->Kookoo_CollectDtmf);
			}
		}
		else {
			$this->Kookoo_response->addPlayText('Sorry, session and events not maintained properly, Thanks you for calling, have nice day',$voiceDelay);
			$this->Kookoo_response->addHangup();
		}
		$this->Kookoo_response->getXML();
		$this->Kookoo_response->send();
	}
}