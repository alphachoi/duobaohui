<?php
namespace Snake\Libs;

abstract class Controller {

	protected $mode = 'json';
	protected $request = NULL;
    protected $userSession = NULL;
	protected $head = 200;
	protected $view = "";
	protected $error_code = 0;
	protected $message = 'OK';

	protected $client = NULL;
	protected $mClient = NULL;
	/**
	 * Subclasses must implement this method to route requests.
	 */
	abstract public function run();

	public function __construct(\Snake\Libs\Base\HttpRequest $request, $userSession, $mode) {
		$this->request = $request;
        $this->userSession = $userSession;
		$this->mode = $mode;
		$this->client = \Snake\Libs\Base\ZooClient::getClient($this->userSession['user_id']);
		$this->mClient = \Snake\Libs\Base\MultiClient::getClient($this->userSession['user_id']);
		$this->checkUserValid();
	}

	private function checkUserValid() {
		if (('xml' == $this->mode || 'captcha' == $this->mode || 'u' == $this->mode) && FALSE == $this->checkHostAuthorized()) {
			$this->setError(401, 401, 'Unauthorized');
			$this->echoView();
		}
	}

	private function checkHostAuthorized() {
		$TokenAuthed = new \Snake\Package\Permission\TokenAuthed($this->userSession, get_called_class());
		return $TokenAuthed->getAuthorized();
	}

	public function checkStatusValid() {
		if (200 == $this->head) {
			return TRUE;
		}
		return FALSE;
	}

	public function getView() {
		return $this->view;
	}

	public function echoView() {
		$this->echoHeader();
		if ($this->mode == 'ht') {
			$func = 'formatJson';
		}
		else {
			$func = 'format' . ucwords($this->mode);
		}
		$output = $this->$func();
		echo $output;
	}

	private function formatJson() {
		if (200 === $this->head) {
			$response = $this->view;
		}
		else {
			$response = array(
				'error_code' => $this->error_code,
				'message' => $this->message,
			);
		}
		return self::jsonize($response);
	}

	private function formatXml() {
		if (200 === $this->head) {
			$response = $this->view;
		}
		else {
			$response =
				'<?xml version="1.0" encoding="utf-8" ?>' .
				'<error>' . 
				'<error_code=' .  $this->error_code . '>' . 
				'<message=' .  $this->message . '>' .
				'</error>';
		}
		return $response;
		return self::xmlize($response);
	}

	private function formatCaptcha() {
		if (200 === $this->head) {
			$response = $this->view;
		}
		else {
			return '';
		}
		ob_clean();
		imagejpeg($response, null, 90);
		imagedestroy($response);
	}


	protected function setError($head = 200, $errorCode = 0, $message = 'OK') {
		$this->head = $head;
		$this->error_code = $errorCode;
		$this->message = $message;
	}

	protected function echoHeader() {
		if (200 == $this->head) {
			switch ($this->mode) {
				case 'json' :
				case 'ht' :
					header('Content-Type: text/plain; charset=UTF-8');
					break;
				case 'captcha' :
					ob_clean();
					header('Content-type: image/jpeg;');
					header("Cache-Control: no-cache");
					header("Expires: -1");
					break;
				default:
					header('Content-Type: text/xml; charset=UTF-8');
			}
			return;
		}
		$this->setHeaderByHttpStatusCode($this->head);
	}

	protected function setHeaderByHttpStatusCode($code) {
		$codes = array(
			'400' => '400 Bad Request',
			'401' => '401 Unauthorized',
			'404' => '404 Not Found',
		);

		if (!isset($codes[$code])) {
			throw new \Exception(sprintf("Unknown HTTP status code: %s.", $code));
		}

		header("HTTP/1.1 {$codes[$code]}");
	}

	protected function jsonize($data) {
		return \Snake\Libs\Base\Utilities::jsonEncode($data);
	}

	protected function xmlize($data) {
		$xmlHead = '<?xml version="1.0" encoding="utf-8" ?>';
		return $xmlHead . $data;
	}
}
