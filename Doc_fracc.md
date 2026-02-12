# Documentacion tecnica sobre la pagina del fraccionamiento #

En este documento se pretende realizar la documentacion de como funciona internamente el flujo de informacion del fraccionamiento
en cada una de las paginas de manera breve para que funcione como mapa


## Dashboard ##

En esta pantalla se muestran indicadores sobre valores relevantes para el fraccionamiento, para que la informacion este de manera mas accesible 

## Herramientas ##

### Mapa del fraccionamiento ###

En esta vista podemos ver de manera simple las calles de fraccionamiento para hacer una busqueda de los **Domicilios** de manera intuitiva 

La manera en la que se listan es una consulta a la base de datos a la tabla de calles para mostrarlas de manera visual, y por GET 
mandar el id para en la siguiente vista poder listar los **Domicilios** registrados que coincidan con el seleccionado

### Nuevo domicilio ###

En el caso de que el **Domicilio** que queremos localizar no existe, pues se daria de alta en el sistema, subiendolo a la tabla de **Domicilios** y registrando, nombre, telefono, calle y numero de casa

desde ahi ya todo el sistema se encarga de acomodarlo donde debe de ir exactamente

### Nuevo Usuario ###

Un **Usuario** es diferente a un **Domicilio**, ya que cada **Domicilio** puede tener varios **Usuarios**, mientras que 1 **Usuario** solo puede estar vinculado a 1 **Domicilio**, mismo que se tiene que autorizar explicitamente por un **Usuario** administrador, que tambien queda registrado cual es **Usuario** que autorizo a otro **Usuario** en cierto **Domicilio**


### Crear evento ###

## Reportes ##
## Registros ##
## Caseta ##