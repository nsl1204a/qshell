drop table inc_bloque
drop table inc_bloque_ejecucion;
drop table inc_estado_ejecucion;
drop table inc_estado_proceso;
drop table inc_parametro;
drop table inc_proceso;
drop table inc_proceso_ejecucion;
drop table inc_tipo_dato;
drop table inc_tipo_parametro;
drop table inc_tipo_proceso;
drop table segu_aplicacion 
drop table segu_base_datos;
drop table segu_funcion
drop table segu_perfil
drop table segu_perfil_funcion
drop table segu_perfil_usuario
drop table segu_usuario
drop table segu_usuario_bbdd ;
drop table segu_usuario_internet;

-- tipos de bloques
create table inc_tipo_bloque (
 cod_tipo_bloque                           varchar(6) not null 
 ,des_tipo_bloque                           varchar(50) not null 
 ,PRIMARY KEY (cod_tipo_bloque)    
)
ENGINE = InnoDB;

-- estados de procesos
create table inc_estado_proceso(
 cod_estado_proceso                        varchar(6) not null 
 ,des_estado_proceso                        varchar(50) not null 
 ,PRIMARY KEY (cod_estado_proceso)    
)
ENGINE = InnoDB; 

-- tipos de procesos
create table inc_tipo_proceso(
 cod_tipo_proceso                          varchar(6) not null 
 ,des_tipo_proceso                          varchar(50) not null 
 ,PRIMARY KEY (cod_tipo_proceso)    
)
ENGINE = InnoDB; 

-- tipos de datos de parametros
create table inc_tipo_dato(
 cod_tipo_dato                             varchar(6) not null 
 ,des_tipo_dato                             varchar(50) not null 
 ,PRIMARY KEY (cod_tipo_dato)      
)
ENGINE = InnoDB; 

-- tipos de parametros 
create table inc_tipo_parametro(
 cod_tipo_parametro                        varchar(6) not null 
 ,des_tipo_parametro                        varchar(50) not null 
 ,PRIMARY KEY (cod_tipo_parametro)      
)
ENGINE = InnoDB; 

-- estados de ejecucion
create table inc_estado_ejecucion(
 cod_estado_ejecucion                      varchar(6) not null 
 ,des_estado_ejecucion                      varchar(50) not null 
 ,PRIMARY KEY (cod_estado_ejecucion)       
)
ENGINE = InnoDB; 

-- usuarios
create table segu_usuario(
 username                                   varchar(30) not null
 ,f_alta                                     datetime not null
 ,nombre                                             varchar(50)
 ,primer_apellido                                    varchar(50)
 ,segundo_apellido                                   varchar(50)
 ,observacion                                        varchar(254)
 ,f_baja                                             datetime
 ,ind_generico                               varchar(1) not null
 ,ind_organizacion                                   varchar(1)
 ,PRIMARY KEY (username) 
)
ENGINE = InnoDB;

-- usuarios internet que no tienen cuenta directa en la base de datos y necesitan una contraseña 
-- se conectan a la bb.dd con un usuario generico.
create table segu_usuario_internet(
 username                                   varchar(30) not null
,password                                   varchar(50) not null
,PRIMARY KEY (username) 
,CONSTRAINT fk_usuint_usu FOREIGN KEY (username) REFERENCES segu_usuario(username)
)
ENGINE = InnoDB;

-- bases de datos
create table segu_base_datos(
 cod_bbdd                                  varchar(7) not null 
 ,des_bbdd                                  varchar(50) not null 
 ,cod_proveedor                         varchar(8) not null 
 ,des_conexion                            varchar(50) not null 
 ,PRIMARY KEY (cod_bbdd)  
)
ENGINE = InnoDB;

-- aplicaciones
create table segu_aplicacion (
 cod_aplicacion                             varchar(6) not null
 ,des_aplicacion                            varchar(50) not null 
 ,observacion                                        varchar(254)
 ,PRIMARY KEY (cod_aplicacion)
)
ENGINE = InnoDB;

-- funciones de aplicaciones
create table segu_funcion(
  cod_aplicacion                            varchar(6) not null 
 ,cod_funcion                                varchar(6) not null 
 ,des_funcion                                varchar(50) not null 
 ,PRIMARY KEY (cod_aplicacion, cod_funcion)  
 ,CONSTRAINT fk_funcion_aplicacion FOREIGN KEY (cod_aplicacion) REFERENCES segu_aplicacion(cod_aplicacion)
)
ENGINE = InnoDB;

-- perfiles de aplicaciones
create table segu_perfil (
 cod_aplicacion                             varchar(6) not null
 ,cod_perfil                             varchar(6) not null
 ,des_perfil                             varchar(50) not null
 ,PRIMARY KEY (cod_aplicacion, cod_perfil)    
 ,CONSTRAINT fk_perfil_aplicacion FOREIGN KEY (cod_aplicacion) REFERENCES segu_aplicacion(cod_aplicacion)
)
ENGINE = InnoDB;

-- funciones en perfiles
create table segu_perfil_funcion (
 cod_aplicacion                             varchar(6) not null
 ,cod_perfil                             varchar(6) not null
 ,cod_funcion                             varchar(6) not null
 ,PRIMARY KEY (cod_aplicacion, cod_perfil, cod_funcion)     
 ,CONSTRAINT fk_perfil_funcion_perfil FOREIGN KEY (cod_aplicacion, cod_perfil) REFERENCES segu_perfil (cod_aplicacion, cod_perfil) 
 ,CONSTRAINT fk_perfil_funcion_funcion FOREIGN KEY (cod_aplicacion, cod_funcion) REFERENCES segu_funcion (cod_aplicacion, cod_funcion) 
)
ENGINE = InnoDB;

-- usuarios en perfiles
create table segu_perfil_usuario (
 cod_aplicacion                             varchar(6) not null
 ,cod_perfil                             varchar(6) not null
 ,username	                             varchar(30) not null
 ,f_alta									datetime not null
 ,ind_virtual							varchar(1) not null
 ,PRIMARY KEY (cod_aplicacion, cod_perfil, username)     
 ,CONSTRAINT fk_perfil_usuario_perfil FOREIGN KEY (cod_aplicacion, cod_perfil) REFERENCES segu_perfil (cod_aplicacion, cod_perfil) 
 ,CONSTRAINT fk_perfil_usuario_usuario FOREIGN KEY (username) REFERENCES segu_usuario (username) 

)
ENGINE = InnoDB;

-- procesos en que se ejecutan funciones
create table inc_proceso(
  cod_aplicacion                            varchar(6) not null 
 ,cod_funcion                               varchar(6) not null 
 ,password                                           varchar(50)
 ,cod_estado_proceso                   varchar(6) not null 
 ,cod_tipo_proceso                       varchar(6) not null 
 ,cod_bbdd                                  varchar(7) not null 
 ,username                                           varchar(30)
 ,PRIMARY KEY (cod_aplicacion, cod_funcion)   
 ,CONSTRAINT fk_proceso_estado FOREIGN KEY (cod_estado_proceso) REFERENCES inc_estado_proceso (cod_estado_proceso) 
 ,CONSTRAINT fk_proceso_tipo FOREIGN KEY (cod_tipo_proceso) REFERENCES inc_tipo_proceso (cod_tipo_proceso) 
)
ENGINE = InnoDB;

-- bloques de funciones
create table inc_bloque(
 cod_aplicacion                            varchar(6) not null 
 ,cod_funcion                                varchar(6) not null 
 ,cod_bloque                                 varchar(6) not null 
 ,des_bloque                                varchar(50) not null 
 ,orden                                      integer(2) not null 
 ,bloque                                    text not null 
 ,cod_tipo_bloque                   varchar(6) not null 
 ,observacion                                        varchar(254)
 ,PRIMARY KEY (cod_aplicacion ,cod_funcion ,cod_bloque)
 ,CONSTRAINT fk_bloque_funcion FOREIGN KEY (cod_aplicacion, cod_funcion) REFERENCES inc_proceso (cod_aplicacion, cod_funcion) 
     
)
ENGINE = InnoDB;

-- parametros de bloques de funciones
create table inc_parametro(
 cod_aplicacion                            varchar(6) not null 
 ,cod_funcion                                varchar(6) not null 
 ,cod_bloque                                 varchar(6) not null 
 ,cod_parametro                             varchar(6) not null 
 ,des_parametro                             varchar(50) not null 
 ,longitud                                           integer(4)
 ,orden                                      integer(2) not null 
 ,valor_defecto                                      varchar(50)
 ,cod_tipo_parametro                 varchar(6) not null 
 ,cod_tipo_dato                           varchar(6) not null 
 ,PRIMARY KEY (cod_aplicacion,cod_funcion,cod_bloque, cod_parametro)     
 ,CONSTRAINT fk_parametro_bloque FOREIGN KEY (cod_aplicacion, cod_funcion, cod_bloque) REFERENCES inc_bloque (cod_aplicacion, cod_funcion, cod_bloque) 
)
ENGINE = InnoDB; 

-- ejecuciones de procesos
create table inc_proceso_ejecucion(
 cod_aplicacion                            varchar(6) not null 
 ,cod_funcion                               varchar(6) not null 
 ,f_ejecucion                               datetime not null 
 ,cod_estado_ejecucion             varchar(6) not null 
 ,username                                  varchar(30) not null 
 ,observacion                                        varchar(254)
 ,PRIMARY KEY (cod_aplicacion,cod_funcion, f_ejecucion)       
 ,CONSTRAINT fk_procex_funcion FOREIGN KEY (cod_aplicacion, cod_funcion) REFERENCES inc_proceso (cod_aplicacion, cod_funcion) 
 ,CONSTRAINT fk_procex_estado FOREIGN KEY (cod_estado_ejecucion) REFERENCES inc_estado_ejecucion (cod_estado_ejecucion) 
 
)
ENGINE = InnoDB; 

-- ejecucion de bloques
create table inc_bloque_ejecucion(
 cod_aplicacion                            varchar(6) not null 
 ,cod_funcion                                varchar(6) not null 
 ,cod_bloque                                 varchar(6) not null 
 ,f_ejecucion_proceso                  datetime not null 
 ,f_ejecucion_bloque                     datetime not null 
 ,mensaje_error                                      text
 ,PRIMARY KEY (cod_aplicacion,cod_funcion,cod_bloque,f_ejecucion_proceso,f_ejecucion_bloque)        
 ,CONSTRAINT fk_bloquex_bloque FOREIGN KEY (cod_aplicacion, cod_funcion, cod_bloque) REFERENCES inc_bloque (cod_aplicacion, cod_funcion, cod_bloque) 
 ,CONSTRAINT fk_bloquex_procex FOREIGN KEY (cod_aplicacion, cod_funcion) REFERENCES inc_proceso_ejecucion (cod_aplicacion, cod_funcion) 
)
ENGINE = InnoDB; 
