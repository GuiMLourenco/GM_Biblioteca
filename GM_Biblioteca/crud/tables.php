<?php
// Metadados das tabelas para gerar CRUD genérico
// Ajuste conforme necessário

return [
	'autores' => [
		'label' => 'Autores',
		'pk' => 'au_cod',
		'auto_increment' => true,
		'columns' => [
			'au_cod' => ['label' => 'Código', 'type' => 'number', 'required' => false, 'readonly' => true],
			'au_nome' => ['label' => 'Nome', 'type' => 'text', 'required' => true],
			'au_pais' => ['label' => 'País', 'type' => 'text', 'required' => true, 'fk' => ['table' => 'paises', 'value' => 'pa_pais', 'label' => 'pa_pais']],
		],
	],
	'generos' => [
		'label' => 'Géneros',
		'pk' => 'genero',
		'auto_increment' => false,
		'columns' => [
			'genero' => ['label' => 'Género', 'type' => 'text', 'required' => true],
		],
	],
	'edicoes' => [
		'label' => 'Edições',
		'pk' => 'edc_edicao',
		'auto_increment' => false,
		'columns' => [
			'edc_edicao' => ['label' => 'Edição', 'type' => 'number', 'required' => true],
		],
	],
	'editoras' => [
		'label' => 'Editoras',
		'pk' => 'edt_cod',
		'auto_increment' => true,
		'columns' => [
			'edt_cod' => ['label' => 'Código', 'type' => 'number', 'required' => false, 'readonly' => true],
			'edt_nome' => ['label' => 'Nome', 'type' => 'text', 'required' => true],
			'edt_pais' => ['label' => 'País', 'type' => 'text', 'required' => true, 'fk' => ['table' => 'paises', 'value' => 'pa_pais', 'label' => 'pa_pais']],
			'edt_morada' => ['label' => 'Morada', 'type' => 'text', 'required' => true],
			'edt_cod_postal' => ['label' => 'Código Postal', 'type' => 'text', 'required' => true, 'fk' => ['table' => 'codigos_postais', 'value' => 'cod_postal', 'label' => 'cod_postal']],
			'edt_email' => ['label' => 'Email', 'type' => 'email', 'required' => true],
			'edt_tlm' => ['label' => 'Telemóvel', 'type' => 'number', 'required' => true],
		],
	],
	'livros' => [
		'label' => 'Livros',
		'pk' => 'Li_cod',
		'auto_increment' => true,
		'columns' => [
			'Li_cod' => ['label' => 'Código', 'type' => 'number', 'required' => false, 'readonly' => true],
			'Li_ISBN' => ['label' => 'ISBN', 'type' => 'text', 'required' => true],
			'Li_titulo' => ['label' => 'Título', 'type' => 'text', 'required' => true],
			'Li_genero' => ['label' => 'Género', 'type' => 'text', 'required' => false, 'fk' => ['table' => 'generos', 'value' => 'genero', 'label' => 'genero']],
			'Li_ano' => ['label' => 'Ano', 'type' => 'text', 'required' => false],
			'Li_edicao' => ['label' => 'Edição', 'type' => 'text', 'required' => false, 'fk' => ['table' => 'edicoes', 'value' => 'edc_edicao', 'label' => 'edc_edicao']],
			'Li_editora' => ['label' => 'Editora', 'type' => 'text', 'required' => false, 'fk' => ['table' => 'editoras', 'value' => 'edt_nome', 'label' => 'edt_nome']],
		],
	],
	'livro_exemplar' => [
		'label' => 'Exemplares',
		'pk' => 'Lex_cod',
		'auto_increment' => true,
		'columns' => [
			'Lex_cod' => ['label' => 'Código', 'type' => 'number', 'required' => false, 'readonly' => true],
			'Lex_Li_cod' => ['label' => 'Livro', 'type' => 'number', 'required' => true, 'fk' => ['table' => 'livros', 'value' => 'Li_cod', 'label' => 'Li_titulo']],
			'Lex_estado' => ['label' => 'Estado', 'type' => 'text', 'required' => true, 'fk' => ['table' => 'estados', 'value' => 'es_estado', 'label' => 'es_estado']],
			'Lex_disponivel' => ['label' => 'Disponível', 'type' => 'boolean', 'required' => true],
			'Lex_permrequisicao' => ['label' => 'Permite Requisição', 'type' => 'boolean', 'required' => false],
		],
	],
	'requisicoes' => [
		'label' => 'Requisições',
		'pk' => 're_cod',
		'auto_increment' => true,
		'columns' => [
			're_cod' => ['label' => 'Código', 'type' => 'number', 'required' => false, 'readonly' => true],
			're_ut_cod' => ['label' => 'Utente', 'type' => 'number', 'required' => true, 'fk' => ['table' => 'utentes', 'value' => 'ut_cod', 'label' => 'ut_nome']], 
	        're_lex_cod' => ['label' => 'Código do exemplar', 'type' => 'number', 'required' => true, 'fk' => ['table' => 'livro_exemplar', 'value' => 'Lex_cod', 'label' => 'Lex_cod']], 
			're_datarequesicao' => ['label' => 'Data Requisição', 'type' => 'date', 'required' => true],
			're_datadevolucao' => ['label' => 'Data Devolução', 'type' => 'date', 'required' => true],
			're_devolvido' => ['label' => 'Devolvido', 'type' => 'boolean', 'required' => false],
		],
	],
	'utentes' => [
		'label' => 'Utentes',
		'pk' => 'ut_cod',
		'auto_increment' => true,
		'columns' => [
			'ut_cod' => ['label' => 'Código', 'type' => 'number', 'required' => false, 'readonly' => true],
			'ut_nome' => ['label' => 'Nome', 'type' => 'text', 'required' => true],
			'ut_email' => ['label' => 'Email', 'type' => 'email', 'required' => true],
			'ut_ano' => ['label' => 'Ano', 'type' => 'number', 'required' => true],
			'ut_turma' => ['label' => 'Turma', 'type' => 'text', 'required' => true],
		],
	],
	'codigos_postais' => [
		'label' => 'Códigos Postais',
		'pk' => 'cod_postal',
		'auto_increment' => false,
		'columns' => [
			'cod_postal' => ['label' => 'Código Postal', 'type' => 'text', 'required' => true],
			'cod_localidade' => ['label' => 'Localidade', 'type' => 'text', 'required' => true],
		],
	],
	'estados' => [
		'label' => 'Estados',
		'pk' => 'es_estado',
		'auto_increment' => false,
		'columns' => [
			'es_estado' => ['label' => 'Estado', 'type' => 'text', 'required' => true],
		],
	],
	'paises' => [
		'label' => 'Países',
		'pk' => 'pa_pais',
		'auto_increment' => false,
		'columns' => [
			'pa_pais' => ['label' => 'País', 'type' => 'text', 'required' => true],
		],
	],
];
