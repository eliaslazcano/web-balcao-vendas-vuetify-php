create table clientes
(
	id int unsigned auto_increment
		primary key,
	nome varchar(128) null,
	digital mediumtext null comment 'string que identifica a impressao digital',
	criado_em datetime default current_timestamp() not null,
	deletado_em datetime null
);

create table configuracoes
(
	id int unsigned auto_increment
		primary key,
	criado_em datetime default current_timestamp() not null,
	biometria_nitgen tinyint(1) default 0 null comment 'utilizar scan de digital da Nitgen'
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

create table vendas
(
	id int unsigned auto_increment
		primary key,
	cliente varchar(128) null,
	cadastro int unsigned null comment 'vincula a venda a um cliente cadastrado',
	credito decimal(8,2) default 0.00 not null,
	criado_em datetime default current_timestamp() null,
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
	quantidade float null,
	valor decimal(8,2) null,
	constraint venda_items_produtos_id_fk
		foreign key (produto) references produtos (id)
			on update cascade on delete set null,
	constraint venda_itens_vendas_id_fk
		foreign key (venda) references vendas (id)
			on update cascade on delete cascade
);
