define(["jquery",
        "local_social_course/vue", 
        "local_social_course/vuetify", 
        "local_social_course/axios",
        "local_social_course/moment",
        "local_social_course/emojionearea",
        "local_social_course/uploadfilecomponent",
      ], 
function($, Vue, Vuetify, Axios, Moment, Emojionearea, Uploadfile) {
  "use strict";
  const ALL = -1
  const WITHOUT = 0

  function add_initial_properties (data){
    data.enrolled.forEach(user => {
      user.is_recipient = true
    })
    return data
  }

  function init(data) {
    data = add_initial_properties(data)
    Vue.use(Vuetify)
    Vue.component('upload-file',Uploadfile)
    Vue.config.productionTip = false;
    new Vue({
      delimiters: ["[[", "]]"],
      el: "#publications",
      vuetify: new Vuetify(),
      data(){  
        return {
          upload : {

          },
          course : data.course,
          user : data.user,
          selection : {
            is_custom : false,
            type : "all",
            group_id : ALL,
            role_id : ALL
          },
          recipient : {
            selecting : false,
            enrolled : data.enrolled,
            table : {
              headers : [
                {
                  text: '',
                  align: 'left',
                  sortable: false,
                  value: 'picture',
                },
                {
                  text: 'Nombres',
                  align: 'left',
                  sortable: true,
                  value: 'firstname',
                },
                {
                  text: 'Apellidos',
                  align: 'left',
                  sortable: true,
                  value: 'lastname',
                },
                {
                  text: 'Selección',
                  align: 'center',
                  sortable: true,
                  value: 'is_recipient',
                }
              ]
            }
          },
          group : {
            all : data.groups,
            selected : ALL
          },
          role : {
            availables : data.available_roles,
            selected : ALL
          },
          publication : {
            new : {
              images : {
                uploaded : [],
                pending : []
              },
              resources : [],
              errors : []
            }
          },
          search : null,
        }
      },
      mounted() {
        $(".emoji-picker").emojioneArea({
          pickerPosition: "right",
          tonesStyle: "bullet",
          placeholder: "¿Que tienes en mente?"
        });
        this.add_default_availables_roles()
        this.add_default_group()
      },
      computed : {
        exist_attachments(){
          let exist = this.publication.new.images.pending.length > 0 || 
                      this.publication.new.images.uploaded.length > 0
          return exist
        },
        selection_name(){
          var name = "all"
          if(this.selecting_all()){
            name = 'Todos'
          }else if (this.selecting_group()){
            name = this.get_group_selection_name()
          }else if (this.selecting_custom()){
            name = 'Personalizado'
          }
          return name
        },
        groups(){
          let groups = this.group.all
          return groups
        },
        available_roles(){
          this.role.availables.forEach(role => {
            if(role.name.length == 0){
              role.name = role.shortname
            }
          })
          return this.role.availables
        },
        recipients(){
          var users = this.filter_users_by_group(this.recipient.enrolled)
          users = this.filter_users_by_role(users)
          if(this.search){
            users = this.filter_users_by_search(users)
          }
          return users
        }
      },
      methods : {
        selecting_all(){
          var selection = this.group.selected == ALL && this.role.selected == ALL && !this.selection.is_custom
          return selection
        },
        selecting_group(){
          var selection = this.selection.type == 'group' && !this.selection.is_custom && !this.selecting_all()
          return selection
        },
        selecting_custom(){
          var selection = this.selection.is_custom
          return selection
        },
        exist_users_without_group(){
          var finded = this.recipient.enrolled.find(user => {return user.groups == 0})
          let without_group = false
          if(finded){
            without_group = true
          } 
          return without_group
        },
        filter_users_by_group(users){
          var filtered = []
          if(this.group.selected == ALL){
            filtered = users
          }else if(this.group.selected == WITHOUT){
            if(this.exist_users_without_group()){
              filtered = this.get_users_without_group()
            }
          }else{
            users.forEach(user => {
              let user_belongs_group = user.groups.some(group => { return group.id == this.group.selected })
              if(user_belongs_group){
                filtered.push(user)
              }
            })
          }
          return filtered
        },

        get_users_without_group(){
          let users = this.recipient.enrolled.filter(user => { return user.groups.length == 0})
          return users
        },
        
        filter_users_by_role(users){
          var filtered = []
          if(this.role.selected == ALL){
            filtered = users
          }else{
            users.forEach(user => {
              let user_belongs_role = user.roles.some(role => { return role.id == this.role.selected})
              if(user_belongs_role){
                filtered.push(user)
              }
            })
          }
          return filtered
        },

        filter_users_by_search(users){
          var sentence = this.search.toLowerCase()
          console.log(users)
          users = users.filter(user => {
            return user.firstname.toLowerCase().includes(sentence) || user.lastname.toLowerCase().includes(sentence)
          })
          return users
        },

        add_default_availables_roles(){
          let role = new Object
          role.id = -1
          role.archetype = "all"
          role.description = ""
          role.name = "Todos"
          role.shortname = ""
          role.sortorder = 0
          this.role.availables.unshift(role)
        },

        add_default_group(){
          if(this.exist_users_without_group()){
            let group = new Object
            group.id = 0
            group.name = "Sin grupo"
            group.idnumber = "withoutgroup"
            this.group.all.unshift(group)
          }
          let group = new Object
          group.id = -1
          group.name = "Todos"
          group.idnumber = "all"
          this.group.all.unshift(group)
        },

        accepted_mime_types(){
          var mime_types = ["image/png", "image/jpeg"]
          mime_types = mime_types.join(", ")
          return mime_types
        },

        addImage(files){
          // console.log("filesfilesfiles", files)  
          // var route = 'http://cvirtual.cl/local/social_course/ajax.php'
          files.forEach((file) => {
            var image = new Object
            image.raw = file
            image.uploaded = false
            image.url = new Object
            image.url.local = URL.createObjectURL(file)
            image.url.server = null
            // console.log("FILEFILEFILE", file)
            // let config = {
            //   onUploadProgress: progressEvent => {
            //     let percentCompleted = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
            //     console.log("percentCompleted", percentCompleted)
            //   }
            // }

            // var formData = new FormData();
            // formData.append('resource', file, file.filename);
            // formData.append('a', 'uploadresource');
            // formData.append('uid', 2);
            // formData.append('cid', 2);

            // Axios.post(route, formData, config)
            // .then(response => console.log('response',response))

            this.publication.new.images.pending.unshift(image)
          })
        },

        remove_image(index){
          this.publication.new.images.uploaded.splice(index, 1)
        },
        
        update_recipient(person){
          this.set_custom_preselection()
          if(typeof(person.is_recipient) == "undefined"){
            person.is_recipient = true
          }
          person.is_recipient = !person.is_recipient
        },

        set_custom_preselection(){
          this.selection.is_custom = true
          this.selection.type = "custom"
          this.selection.group_id = null
          this.selection.role_id = null
        },

        select_recipients_from_filters(){
          this.set_group_selection()  
          var users = this.filter_users_by_group(this.recipient.enrolled)
          users = this.filter_users_by_role(users)
          this.clear_recipients()
          this.mark_as_recipients(users)
        },

        set_group_selection(){
          this.selection.type = "group"
          this.selection.group_id = this.group.selected 
          this.selection.role_id = this.role.selected
          this.selection.is_custom = false
        },

        get_group_selection_name(){
          var name = "Role / Group"
          if(this.role.selected == ALL){
            let group = this.find_group(this.group.selected)
            name = group.name
          }else if(this.group.selected == ALL){
            let role = this.find_role(this.role.selected)
            name = role.name
          }else{
            let group = this.find_group(this.group.selected)
            let role = this.find_role(this.role.selected)
            name = `${role.name} / ${group.name}`
          }
          return name
        },

        find_group(id){
          let group = this.group.all.find( group => { return group.id == id})
          return group
        },

        find_role(id){
          let role = this.role.availables.find( role => { return role.id == id})
          return role
        },

        clear_recipients(){
          this.recipient.enrolled.forEach(user => {
            user.is_recipient = false
          })
        },

        mark_as_recipients(users){
          var users_id = this.extract_from(users, "id")
          this.recipient.enrolled.forEach(user => {
            var exist = users_id.find( userid => { return userid == user.id})
            if(exist){
              user.is_recipient = true
            }
          })
        },
        
        extract_from(values, fieldname){
          values = values.map( value => {return value[fieldname]})
          return values
        },

        recipients_summary(){
          let selected = this.get_selected_recipients()
          let message = `<span class="caption">Esta publicación se compartirá con: 
                         <strong>${selected.length}</strong> personas.</span>`
          return message
        },

        get_selected_recipients(){
          let recipients = this.recipient.enrolled.filter(user => {return user.is_recipient})
          return recipients
        },

        get_recipients_ids(){
          let recipients = this.get_selected_recipients()
          recipients = recipients.map( recipient => {return recipient.id})
          return recipients
        },

        get_publication_content(){
          let content = document.querySelector('#comment-publication').value
          return content 
        },

        clear_publication(){
          $('#comment-publication').val("")
          $('.emojionearea-editor').html("")
        },

        publishing(){
          let route = `${M.cfg.wwwroot}/local/social_course/ajax.php`
          let params = {
            a : "createpublication",
            cid : this.course.id,
            uid : this.user.id,
            c : this.get_publication_content(),
            r : this.get_recipients_ids(),
            ts : this.selection.type,
            ns : this.selection_name,
            gs : this.selection.group_id,
            rs : this.selection.role_id, 
          }

          Axios.get(route, {params: params})
          .then(response => {
            if(response.data.valid){
              this.clear_publication()
            }
          })
          .catch(error => {
          })
          .finally(() => {
          });  
        }

      }
    });
  }

  return {
    init : init
  };
});