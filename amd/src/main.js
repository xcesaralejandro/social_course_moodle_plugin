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
          errors : []
        }
      },
      methods : {
        mime_types(joined){
          var mime_types = ["image/png", "image/jpeg"]
          if(joined){
            mime_types = mime_types.join(", ")
          }
          return mime_types
        },

        addImage(files){
          console.log(files)
          files.forEach((file) => {
            this.publication.images.push(URL.createObjectURL(file))
          })
        },
        remove_image(index){
          this.publication.images.splice(index, 1)
        }
      }
    });
  }

  return {
    init : init
  };
});