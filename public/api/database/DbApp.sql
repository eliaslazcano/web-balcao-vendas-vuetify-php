create table cliente_categorias
(
	id int unsigned auto_increment
		primary key,
	nome varchar(100) null,
	deletado_em datetime null
);

create table clientes
(
	id int unsigned auto_increment
		primary key,
	nome varchar(128) null,
	categoria int unsigned null,
	digital mediumtext null comment 'string que identifica a impressao digital',
	criado_em datetime default current_timestamp() null,
	deletado_em datetime null,
	constraint clientes_cliente_categorias_id_fk
		foreign key (categoria) references cliente_categorias (id)
			on update cascade on delete set null
);

create table cliente_emails
(
	id int unsigned auto_increment
		primary key,
	cliente int unsigned not null,
	email varchar(100) null,
	criado_em datetime default current_timestamp() null,
	deletado_em datetime null,
	constraint cliente_emails_clientes_id_fk
		foreign key (cliente) references clientes (id)
			on update cascade on delete cascade
);

create table cliente_enderecos
(
	id int unsigned auto_increment
		primary key,
	cliente int unsigned not null,
	cep varchar(8) null,
	uf varchar(2) null,
	bairro varchar(100) null,
	cidade varchar(100) null,
	logradouro varchar(100) null,
	criado_em datetime default current_timestamp() null,
	deletado_em datetime null,
	constraint cliente_enderecos_clientes_id_fk
		foreign key (cliente) references clientes (id)
			on update cascade on delete cascade
);

create table cliente_observacoes
(
	id int unsigned auto_increment
		primary key,
	cliente int unsigned not null,
	observacao varchar(255) null,
	criado_em datetime default current_timestamp() not null,
	deletado_em datetime null,
	constraint cliente_observacoes_clientes_id_fk
		foreign key (cliente) references clientes (id)
			on update cascade on delete cascade
);

create table cliente_telefones
(
	id int unsigned auto_increment
		primary key,
	cliente int unsigned not null,
	numero varchar(12) null,
	tipo int unsigned default 1 not null comment '1 = fixo, 2 = celular',
	criado_em datetime default current_timestamp() null,
	deletado_em datetime null,
	constraint cliente_telefones_clientes_id_fk
		foreign key (cliente) references clientes (id)
			on update cascade on delete cascade
);

create table despesas
(
	id int unsigned auto_increment
		primary key,
	descricao varchar(100) null,
	valor decimal(8,2) default 0.00 not null,
	observacao varchar(250) null,
	criado_em datetime default current_timestamp() null,
	deletado_em datetime null
);

create table produtos
(
	id int unsigned auto_increment
		primary key,
	nome varchar(128) null,
	codigo varchar(128) null,
	valor decimal(6,2) default 0.00 not null,
	criado_em datetime default current_timestamp() null,
	deletado_em datetime null
);

create table usuarios
(
	id int unsigned auto_increment
		primary key,
	login varchar(64) null,
	senha varchar(32) null,
	nome varchar(192) null,
	email varchar(100) null,
	criado_em datetime default current_timestamp() not null,
	inativado_em datetime default current_timestamp() null,
	constraint login
		unique (login)
);

create table sessoes
(
	id int unsigned auto_increment
		primary key,
	usuario int unsigned not null,
	datahora datetime default current_timestamp() not null,
	ip varchar(15) null,
	agente varchar(196) null,
	chave varchar(32) null,
	constraint sessoes_usuarios_id_fk
		foreign key (usuario) references usuarios (id)
			on update cascade on delete cascade
);

create table vendas
(
	id int unsigned auto_increment
		primary key,
	cliente varchar(128) null,
	cadastro int unsigned null comment 'vincula a venda a um cliente cadastrado',
	credito decimal(8,2) default 0.00 not null,
	nota mediumtext null,
	criado_em datetime default current_timestamp() null,
	encerrado_em datetime null,
	deletado_em datetime null,
	constraint vendas_clientes_id_fk
		foreign key (cadastro) references clientes (id)
			on update cascade on delete set null
);

create table venda_itens
(
	id int unsigned auto_increment
		primary key,
	venda int unsigned not null,
	produto int unsigned null,
	quantidade float not null,
	valor_un decimal(8,2) null,
	valor decimal(8,2) null,
	criado_em datetime default current_timestamp() null,
	constraint venda_items_produtos_id_fk
		foreign key (produto) references produtos (id)
			on update cascade on delete set null,
	constraint venda_itens_vendas_id_fk
		foreign key (venda) references vendas (id)
			on update cascade on delete cascade
);

create table venda_rfids
(
	id int unsigned auto_increment
		primary key,
	rfid varchar(32) null,
	venda int unsigned not null,
	criado_em datetime default current_timestamp() null,
	desvinculado_em datetime null,
	constraint venda_rfids_vendas_id_fk
		foreign key (venda) references vendas (id)
			on update cascade on delete cascade
)
comment 'Dispositivos de identificacao RFIDs vinculados a venda';
