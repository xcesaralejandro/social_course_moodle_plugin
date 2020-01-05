define(['jquery', 'local_social_course/axios',"local_social_course/emojionearea"],
function($, Axios, EmojiOnArea) {
  const ALL = -1
  const WITHOUT = 0
  const createpublication = {
    template : `<v-content id="publication">
                <v-layout column id="create-publication" class="pa-4 pl-6 pr-6">
                  <v-flex pb-2 d-flex justify-center>
                    <span class="title">NUEVA PUBLICACIÓN</span>
                  </v-flex>

                  <v-flex class="mb-2">
                    <textarea id="comment-publication" class="emoji-picker" outlined></textarea>
                  </v-flex>

                  <v-flex v-if="exist_attachments">
                    <v-layout justify-end align-center>
                      <span v-text="attachment_photos_legend()" class="mr-2"></span>
                      <span v-text="current_attachment_photos + '/'"
                            class="current-photo-attachment"></span>
                      <span class="max-photo-attachment" v-text="config.max_attachment_photo"></span>
                    </v-layout>
                    <v-layout id="attachments-container" nowrap class="mt-2">
                      <template v-for="(image, index, key) in publication.new.images">
                        <upload-file :file="image.raw" :position="index" :courseid="course.id" class="ma-2"
                                    :label="upload_file_labels" :authorid="user.id" :key="key"
                                    @loaded="update_load_status" @delete="remove_image"
                                    :maxupload="config.max_attachment_photo"></upload-file>
                      </template>
                    </v-layout>
                  </v-flex>

                  <v-flex d-flex justify-center class="action-separator pt-4">
                    <label for="upload-photo" class="ma-0">
                        <v-flex :class="['label-input-image', 'sc-btn-gray', 'pr-4', 'pl-4', 'pt-1', 'pb-1',
                                        'd-flex', {'max-upload-limited-exceeded' : photo_attachments_exceed_limit()}]">
                          <span class="mr-2 d-flex align-center">IMAGEN</span>
                          <v-icon class="mr-1">add_photo_alternate</v-icon>
                        </v-flex>
                      </label>
                    <v-file-input  v-if="!photo_attachments_exceed_limit()" class="pa-0 ma-0 hidden d-none" @change="addImage" :accept="accepted_mime_types()"
                                    :multiple="false" prepend-icon="camera_alt" id="upload-photo"></v-file-input>
                    <v-btn small outlined class="ml-2 sc-btn-gray" @click="recipient.selecting = true">
                      <v-icon v-if="selecting_all() && !this.selection.is_custom" class="mr-1">public</v-icon>
                      <v-icon v-if="selecting_group()" class="mr-1">people</v-icon>
                      <v-icon v-if="selecting_custom()" class="mr-1">settings_applications</v-icon>
                      <span v-text="selection_name"></span>
                    </v-btn>
                    <v-btn small class="sc-btn-primary ml-2" @click="publishing()"
                          >Publicar<v-icon class="ml-2">publish</v-icon></v-btn>
                  </v-flex>
                </v-layout>

                <template>
                  <v-row justify="center">
                    <v-dialog v-model="recipient.selecting" scrollable max-width="800px" id="recipients-dialog">
                      <v-card>
                        <v-card-title>
                          <v-layout justify-center>
                            <span class="headline">¿Quienes deberian ver esto?</span>
                          </v-layout>
                        </v-card-title>
                        <v-card-text class="pb-0">
                          <v-container class="ma-0 pa-0">

                          <v-row class="ma-0">
                            <v-col cols="12" sm="4">
                              <v-layout column>
                                <h5 class="ma-0">Filtros</h5>
                                <v-divider class="mt-2 mb-2"></v-divider>

                                  <v-select item-text="name" item-value="id" :items="renamed_available_roles"
                                            v-model="role.selected" label="Roles"></v-select>
                                  <v-select item-text="name" item-value="id" :items="groups" v-model="group.selected"
                                            label="Grupos"></v-select>
                                  <v-btn class="mx-2 sc-btn-gray mt-2" @click="select_recipients_from_filters()">
                                    SELECCIONAR
                                  </v-btn>
                                  <v-flex class="text-center" v-html="recipients_summary()"></v-flex>
                              </v-layout>
                            </v-col>
                            <v-col cols="12" sm="8">
                            <v-layout column>
                              <h5 class="ma-0">Listado de usuarios</h5>
                              <v-divider class="mt-2 mb-2"></v-divider>
                              <v-row class="ma-0">
                                <v-col cols="12" sm="6" offset-sm="6">
                                  <v-text-field placeholder="Buscar..." v-model="search" class="search-field"
                                    :clearable="true" clear-icon="clear">
                                    <v-icon slot="append" color="gray">search</v-icon>
                                  </v-text-field>
                                </v-col>
                              </v-row>
                              <v-list subheader class="people-container">
                              <v-list-item v-for="(person, index, key) in recipients" :key="key" @click="update_recipient(person)">
                                <v-list-item-avatar>
                                  <v-img :src="person.pictureurl"></v-img>
                                </v-list-item-avatar>
                                <v-list-item-content>
                                  <v-list-item-title>
                                  <span v-text="person.firstname"></span>
                                  <span v-text="person.lastname"></span>
                                  </v-list-item-title>
                                </v-list-item-content>
                                <v-list-item-icon>
                                  <i class="material-icons" v-if="person.is_recipient">done</i>
                                  <i class="material-icons" v-else>add_circle_outline</i>
                                </v-list-item-icon>
                              </v-list-item>
                            </v-list>
                            </v-layout>
                            </v-col>
                          </v-row>
                          </v-container>
                          </v-card-text>
                        <v-card-actions>
                          <v-layout d-flex justify-center>
                          <v-btn class="sc-btn-primary ma-0" small @click="recipient.selecting = false">Cerrar</v-btn>
                        </v-layout>
                        </v-card-actions>
                      </v-card>
                    </v-dialog>
                  </v-row>
                </template>
              </v-content>
    `,
    mounted(){
      $(".emoji-picker").emojioneArea({
        pickerPosition: "bottom",
        tonesStyle: "bullet",
        search : false,
        placeholder: "¿Que tienes en mente?"
      });
      this.add_default_availables_roles()
      this.add_default_group()
    },
    props : ['config', 'course', 'user', 'available_roles', 'enrolled', 'groups'],
    data(){
      return {
        selection : {
          is_custom : false,
          type : "all",
          group_id : ALL,
          role_id : ALL
        },
        recipient : {
          selecting : false,
          enrolled : this.enrolled,
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
          all : this.groups,
          selected : ALL
        },
        role : {
          availables : this.available_roles,
          selected : ALL
        },
        publication : {
          new : {
            images : [],
            resources : [],
            errors : []
          }
        },
        search : null,
      }
    },
    computed : {
        renamed_available_roles(){
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
        },
      exist_attachments(){
        let exist = this.publication.new.images.length > 0
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
      current_attachment_photos(){
        let count = this.publication.new.images.length
        return count
      },
      upload_file_labels(){
        let labels = {status : {inprogress : 'Subiendo...', success : 'Almacenado', error : 'Error'},
                     delete : 'eliminar'}
        return labels
      },
    },
    methods : {
      publishing(){
        if(!this.uploads_finished()){
          this.errors.push("Aún no se han subido todos los ficheros al servidor, espera a que este proceso finalice.")
        }
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
      },

      get_publication_content(){
        let content = document.querySelector('#comment-publication').value
        return content
      },

      get_recipients_ids(){
        let recipients = this.get_selected_recipients()
        recipients = recipients.map( recipient => {return recipient.id})
        return recipients
      },

      get_selected_recipients(){
        let recipients = this.recipient.enrolled.filter(user => {return user.is_recipient})
        return recipients
      },

      image_exist(position){
        let exist = typeof(this.publication.new.images[position]) != 'undefined'
        return exist
      },
      photo_attachments_exceed_limit(){
        let current = this.publication.new.images.length
        let exceed = current >= this.config.max_attachment_photo
        return exceed
      },
      accepted_mime_types(){
        var mime_types = ["image/png", "image/jpeg"]
        mime_types = mime_types.join(", ")
        return mime_types
      },
      addImage(files){
        if(this.photo_attachments_exceed_limit()){
          return null
        }
        files.forEach((file) => {
          var image = new Object
          image.raw = file
          image.id = null
          image.url = new Object
          image.url.local = URL.createObjectURL(file)
          image.url.server = null
          this.publication.new.images.push(image)
        })
      },
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
      attachment_photos_legend(){
        let legend = ""
        if(this.photo_attachments_exceed_limit()){
          legend = `Has alcanzado el numero maximo de adjuntos permitidos.`
        }
        return legend
      },
      update_load_status(notice){
        if(this.image_exist(notice.position)){
          let position = notice.position
          let record = notice.resource
          this.publication.new.images[position].id = record.id
          this.publication.new.images[position].url.server = record.path
        }
      },

      remove_image(notice){
        if(this.image_exist(notice.position)){
          let position = notice.position
          this.publication.new.images.splice(position, 1)
        }
      },
      clear_publication(){
        $('#comment-publication').val("")
        $('.emojionearea-editor').html("")
        this.publication.new.images = []
        this.publication.new.resources = []
      },

      uploads_finished(){
        let finished = true
        if(!this.upload_images_finished()){
          finished = false
        }
        return finished
      },

      upload_images_finished(){
        var finished = true
        var publication = this.publication.new
        publication.images.forEach(image => {
          if(!image.id){
            finished = false
            return
          }
        })
        return finished
      },

      recipients_summary(){
        let selected = this.get_selected_recipients()
        let message = `<span>Destinatarios:</span> <strong>${selected.length}</strong> `
        return message
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
        users = users.filter(user => {
          return user.firstname.toLowerCase().includes(sentence) || user.lastname.toLowerCase().includes(sentence)
        })
        return users
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
    }
  }
  return createpublication
});
