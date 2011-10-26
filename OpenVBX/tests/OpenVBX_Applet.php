<?php

class OpenVBX_Applet_TestCase extends OpenVBX_TestCase 
{
	protected $twimlControllerRefl;
	
	public function setUp() 
	{			
		parent::setUp();		
		$this->CI = set_controller('twiml');		
	}
	
	public function tearDown() 
	{
		parent::tearDown();
		$this->setFlow();
		$this->setFlowId();
	}

	/**
	 * Set the flow data
	 * Don't validate the flow data (other than making sure an array is converted to an object)
	 * so that we can null the flow data
	 *
	 * @param string $flow_data 
	 * @return void
	 */
	public function setFlow($flow_data = null) 
	{	
		if (is_array($flow_data)) {
			$flow_data = (object) $flow_data;
		}
		
		$flowProperty = $this->getFlowProperty('flow');
		
		if (is_object($flow_data) && !empty($flow_data->id)) {
			$this->setFlowid($flow_data->id);
		}
		
		$flowProperty->setValue($this->CI, $flow_data);
	}
	
	/**
	 * Set the flowId
	 * Don't validate the flowId as an int so that we can
	 * null the value to reset state
	 *
	 * @param string $id 
	 * @return void
	 */
	public function setFlowId($id = null) {
		$flowIdProperty = $this->getFlowProperty('flow_id');
		$flowIdProperty->setValue($this->CI, $id);
	}
	
	public function setFlowVar($var, $val) {
		$flowProperty = $this->getFlowProperty('flow');
		$flow_data = $flowProperty->getValue($this->CI);

		if (is_object($flow_data)) {
			$flow_data->$var = $val;
		}

		$flowProperty->setValue($this->CI, $flow_data);
	}
	
	protected function getFlowProperty($prop_name) {
		if (empty($this->twimlControllerRefl)) {
			$this->twimlControllerRefl = new ReflectionObject($this->CI);
		}
		
		$prop = $this->twimlControllerRefl->getProperty($prop_name);
		$prop->setAccessible(true);

		return $prop;
	}
	
}