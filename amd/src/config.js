// Por obligación debemos llamar nuestro codigo con define (Debe ser la unica pieza de codigo) no es obligatorio
// especificar el nombre del modulo (por defecto el nombre será el nombre de archivo y el componente) esto se debe a
// que utilizar el define evitará conflictos del componente y se asegura que no posee variables globales que puedan
// dar problemas como colisionar a futuro.

define([],function() {
  "use strict";
  window.requirejs.config({
    paths: {
      "vue" : M.cfg.wwwroot + '/local/social_course/js/vue',
      "vuetify" : M.cfg.wwwroot + '/local/social_course/js/vuetify',
      "axios" : M.cfg.wwwroot + '/local/social_course/js/axios',
      "moment" : M.cfg.wwwroot + '/local/social_course/js/moment',
    },
    shim: {
      'vue' : {exports: 'vue'},
      'vuetify': {deps: ['vue'] , exports: 'vuetify'},
      'axios': {exports: 'axios'},
      'moment': {deps: ['vue'] , exports: 'moment'},
    }
  });
});
