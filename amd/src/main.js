define(["jquery",
        "local_social_course/vue", 
        "local_social_course/vuetify", 
        "local_social_course/axios",
        "local_social_course/moment",
        "local_social_course/emojionearea",
      ], 
function($, Vue, Vuetify, Axios, Moment, Emojionearea) {
  "use strict";
  
  function init(content) {
    Vue.use(Vuetify)
    Vue.config.productionTip = false;

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
      mounted() {
          $(".emoji-picker").emojioneArea({
            pickerPosition: "right",
            tonesStyle: "bullet",
            placeholder: "¿Que tienes en mente?"
          });
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
            this.publication.images.unshift(URL.createObjectURL(file))
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