<?php

function decodificar($ref)
{
	include 'env.php';

	if ($ref == 'Ref001') {
		return json_encode(
			array(
				'ref'           => 'Ref001',
				'name'          => 'E-Book Decoração pra Vida Real',
				'price'         => '13,70',
				'price_type'    => 'FULL',
				'url'           => '/produtos/ebook-roteiro-decoracao-pra-vida-real/',
				'return_url'    => 'https://meuestilodecor.lpages.co/obrigada-ebook',
				'e_product'     => 'Ebook-Roteiro-Decoracao-Pra-Vida-Real.pdf',
				'real_filename' => '1-ebook-rot-pdf',
				'return_mail'   => 'ebookRoteiro',
				)
			);

	} else if ($ref == 'Ref002-30427DD4F4C208390C375E1D96') {
		return json_encode(
			array(
				'ref'           => 'Ref002-30427DD4F4C208390C375E1D96',
				'name'          => 'E-Book Decoração pra Vida Real',
				'price'         => '6,80',
				'price_type'    => 'DISCOUNT',
				'url'           => '/produtos/ebook-roteiro-decoracao-pra-vida-real/',
				'return_url'    => 'https://meuestilodecor.lpages.co/obrigada-ebook',
				'e_product'     => 'Ebook-Roteiro-Decoracao-Pra-Vida-Real.pdf',
				'real_filename' => '1-ebook-rot-pdf',
				'return_mail'   => 'ebookRoteiro',
				)
			);

	} else if ($ref == 'Ref003') {
		return json_encode(
			array(
				'ref'           => 'Ref003',
				'name'          => 'Manual PagSeguro',
				'price'         => '100,40',
				'price_type'    => 'FULL',
				'url'           => '/produtos/ebook-faca-voce-mesmo/',
				'return_url'    => 'https://meuestilodecor.lpages.co/obrigada-ebook',
				'e_product'     => 'manual-pagseguro.pdf',
				'real_filename' => '2-ebook-faca-pdf',
				'return_mail'   => 'ebookRoteiro',
				)
			);

	} else if ($ref == 'Ref004') {
		return json_encode(
			array(
				'ref'           => 'Ref004',
				'name'          => 'Coaching MeuEstiloDecor',
				'price'         => '457,00',
				'price_type'    => 'FULL',
				'url'           => 'URL_DO_PRODUTO',
				'return_url'    => 'https://meuestilodecor.com.br/obrigada-coaching',
				'e_product'     => '',
				'real_filename' => '',
				'return_mail'   => 'coaching',
				)
			);

	} else if ($ref == 'Ref005') {
		return json_encode(
			array(
				'ref'           => 'Ref005',
				'name'          => 'Coaching MeuEstiloDecor',
				'price'         => '457,00',
				'price_type'    => 'FULL',
				'url'           => 'URL_DO_PRODUTO',
				'return_url'    => 'https://meuestilodecor.com.br/obrigada-coaching',
				'e_product'     => '',
				'real_filename' => '',
				'return_mail'   => 'coaching',
				)
			);

	} else if ($ref == 'Ref005-4633AE0B52E469B8AA7C8FC5F3') {
		return json_encode(
			array(
				'ref'           => 'Ref005-4633AE0B52E469B8AA7C8FC5F3',
				'name'          => 'Coaching MeuEstiloDecor com 50% de desconto',
				'price'         => '228,50',
				'price_type'    => 'DISCOUNT',
				'url'           => 'URL_DO_PRODUTO',
				'return_url'    => 'https://meuestilodecor.com.br/obrigada-coaching',
				'e_product'     => '',
				'real_filename' => '',
				'return_mail'   => 'coaching',
				)
			);

	} else if ($ref == 'Ref006') {
		return json_encode(
			array(
				'ref'           => 'Ref006',
				'name'          => 'Oficina de Moodboard São Paulo - março/2018',
				'price'         => '260,00',
				'price_type'    => 'FULL',
				'url'           => '',
				'return_url'    => 'https://meuestilodecor.com.br/obrigada-oficina-moodboard-03-2018',
				'e_product'     => '',
				'real_filename' => '',
				'return_mail'   => 'oficina',
				)
			);

	}
}


