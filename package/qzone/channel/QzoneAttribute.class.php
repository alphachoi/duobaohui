<?php

namespace Snake\Package\Qzone\Channel;

Use \Snake\Package\Cms\CmsType;
Use \Snake\Package\Cms\CmsManage;


class QzoneAttribute {
	
	private $attrInfo = array();
	private $typeInfo = array();
	private $infos = array();

	public function setQzoneType($selectFields = 'id, type_name, imgurl', $pageTpye = 501, $limit = 6) {
		$params = array(
			'page_type' => $pageTpye,
			'orderby' => 'sortno DESC',
			'limit' => $limit
		);
		$data = CmsType::getCmsData($params, $selectFields);
		$this->typeInfo = $data;
		return TRUE;
	}

	public function setQzoneAttribute($selectFields = "id, data_id, page_type, title, contents, linkurl, sortno, type_id", $pageType = 501, $limit = 100) {
		$params = array(
			'page_type' => $pageType,
			'orderby' => 'sortno DESC',
			'limit' => $limit
		);
		$data = CmsManage::getCmsData($params, $selectFields);
		$this->attrInfo = $data;
		return TRUE;
	}

	public function sortType() {
		$data = array();
		foreach ($this->typeInfo AS $key => $value) {
			$typeId = $this->typeInfo[$key]['id'];
			$data[$typeId] = $this->typeInfo[$key];
			unset($data[$typeId]['id']);
		}
		$this->infos = $data;
		return TRUE;
	}

	public function assembleQzoneAttribute() {
		foreach ($this->attrInfo AS $key => $value) {
			$attr = array();
			$typeId = $this->attrInfo[$key]['type_id'];
			$attr['linkurl'] = $this->attrInfo[$key]['linkurl'];
			$attr['title'] = $this->attrInfo[$key]['title'];
			$attr['red'] = $this->attrInfo[$key]['ext_intfields'];
			$this->infos[$typeId]['words'][] = $attr;
		}
		for ($i = 0; $i < 6; $i++) {
			$data[$i]['type_name'] = rand(1,10);
			$data[$i]['imgurl'] = "http://imgst-office.meilishuo.net/ap/a/dc/08/be6317074df92036cf7b866c9bc7_108_107.jpg";
			for ($j = 0; $j < 15; $j++) {
				$data[$i]['words'][$j]['linkurl'] = "www.meilishuo.com";
				$data[$i]['words'][$j]['title'] = "针织damao毛衣";
				$data[$i]['words'][$j]['red'] = rand(0,1);
			}
		}
		$this->infos = array_values($this->infos);
		$this->infos = $data;
		return TRUE;
	}

	public function getData() {
		return $this->infos;
	}
	

}
