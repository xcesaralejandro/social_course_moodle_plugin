define(["local_social_course/vue",
        "local_social_course/vuetify",
        "local_social_course/createpublicationcomponent",
        "local_social_course/uploadfilecomponent",
        "local_social_course/axios"
      ],
function(Vue, Vuetify, CreatePublication, UploadFile, Axios) {
  "use strict";

  function add_initial_properties (data){
    console.log(data)
    data.enrolled.forEach(user => {
      user.is_recipient = true
    })
    return data
  }

  function init(data) {
    data = add_initial_properties(data)
    Vue.use(Vuetify)
    Vue.component('create-publication', CreatePublication)
    Vue.component('upload-file', UploadFile)
    Vue.config.productionTip = false;
    new Vue({
      delimiters: ["[[", "]]"],
      el: "#publications",
      vuetify: new Vuetify(),
      data(){
        return {
          config : data.config,
          course : data.course,
          user : data.user,
          enrolled : data.enrolled,
          groups : data.groups,
          available_roles : data.available_roles,
          maintabs : 1
        }
      },
      mounted() {
      },
      computed : {

      },
      methods : {
      }
    });
  }
  return {
    init : init
  };
});
