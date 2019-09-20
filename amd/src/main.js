define(["local_social_course/vue", 
        "local_social_course/vuetify", 
        "local_social_course/axios",
        "local_social_course/moment",
      ], 
function(Vue, Vuetify, Axios, Moment) {
  "use strict";
  
  function init(content) {
    console.log("content", content)
    Vue.use(Vuetify)
    new Vue({
      delimiters: ["[[", "]]"],
      el: "#publications",
      vuetify: new Vuetify(),
      data: {
        selecting_recipients : false,
        groups : ['Todos','ex1', 'ex2'],
        preselections : ['Todos','Profesores','Estudiantes'],
        publication : {
          content : '',
          images : [],
          resources : [],
        }
      },
    });
  }

  return {
    init : init
  };
});