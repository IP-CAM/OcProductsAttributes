<?php
class ControllerExtensionModuleProductsAttributes extends Controller {
	public function index($setting) {
		if(!empty($this->request->get['product_id'])) {
			$product_id = $this->request->get['product_id'];			
		} else {
			return;
		}
		if($data = $this->cache->get('products_attributes_' . $product_id . '_' . $setting['attribute'])) {
			return $this->load->view('extension/module/products_attributes', $data);
		}

		$this->load->model('catalog/product');
		$this->load->model('extension/module/products_attributes');

		$this->load->model('tool/image');

		$data['products'] = array();
		
		$attribute = [];

		$attributes = $this->model_catalog_product->getProductAttributes($product_id);

		foreach ($attributes as $attribute_group) {
			foreach ($attribute_group['attribute'] as $attribute_item) {
				if($attribute_item['attribute_id'] == $setting['attribute']) {
					$attribute = $attribute_item;
				}
			}
		}
		if(!$attribute) {
			return;
		}

		$search = [
			'{{attribute.name}}',
			'{{attribute.text}}'
		];

		$replace = [
			$attribute['name'],
			$attribute['text']
		];
		$data['heading_title'] = str_replace($search, $replace, $setting['heading']);
		$products = $this->model_extension_module_products_attributes->getProductsByAttribute($product_id, $attribute, $setting['limit']);
		$results = [];

		foreach ($products as $product) {
			$results[] = $this->model_catalog_product->getProduct($product['product_id']);
		}

		if ($results) {
			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}

				if ($this->config->get('config_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				$data['products'][] = array(
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'rating'      => $rating,
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}
			$this->cache->set('products_attributes_' . $product_id . '_' . $setting['attribute'], $data);
			return $this->load->view('extension/module/products_attributes', $data);
		}
		
	}
}