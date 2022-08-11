create table clientes
(
    id int auto_increment
        primary key,
    nome varchar(128) null,
    criado_em datetime default current_timestamp() not null,
    deletado_em datetime null
);

create table produtos
(
    id int auto_increment
        primary key,
    nome varchar(128) null,
    codigo varchar(128) null,
    valor decimal(6,2) default 0.00 not null,
    criado_em datetime default current_timestamp() null,
    deletado_em datetime null
);

create table vendas
(
    id int auto_increment
        primary key,
    cliente varchar(128) null,
    credito decimal(8,2) default 0.00 not null,
    criado_em datetime default current_timestamp() null
);

create table venda_itens
(
    id int auto_increment
        primary key,
    venda int not null,
    produto int null,
    quantidade float null,
    valor decimal(8,2) null,
    constraint venda_items_produtos_id_fk
        foreign key (produto) references produtos (id)
            on update cascade on delete set null,
    constraint venda_itens_vendas_id_fk
        foreign key (venda) references vendas (id)
            on update cascade on delete cascade
);
