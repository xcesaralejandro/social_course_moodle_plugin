define(['local_social_course/axios'], function(Axios) {
  const uploadfile = {
    template : `
    <v-container class="upload-file-container pa-0">
      <v-layout column>
        <v-flex class="upload-file-header pa-1 d-flex justify-space-around">
          <span v-text="get_title()" v-if="!status.is_success"></span>
          <span v-if="status.is_success" v-text="label.delete" @click="remove()" class="remove-file"></span>
        </v-flex>
        <v-flex v-if="uploading()" class="upload-file-content pa-2 d-flex justify-center align-center">
          <v-progress-circular :rotate="360" :size="100" :width="5" :value="upload.percentage" :color="progress_color()">
            {{ upload.percentage }}
          </v-progress-circular>
        </v-flex>
        <v-flex v-if="status.is_success && is_image()" class="upload-file-content d-flex justify-center align-center">
          <v-img :src="local_url()" aspect-ratio="1"></v-img>    
        </v-flex>
        <v-flex class="upload-file-footer pa-1 text-center">
          <v-layout column class="caption">
          <span v-text="get_size()"></span>
          <span v-text="file_name"></span>
          </v-layout>
        </v-flex>
      </v-layout>
    </v-container>
    `,
    mounted(){
      this.push()
      console.log("file", this.file)
    },
    data(){
      return{
        resource : {
          id : null,
          name : null,
          path : null,
          type : null
        },
        api : {
          route : `${M.cfg.wwwroot}/local/social_course/ajax.php`,
          action : 'uploadresource'
        },
        upload :{
          status : 'inprogress',
          percentage : 0
        }
      }
    },
    props : ['file','position', 'courseid', 'authorid', 'label'],
    computed : {
      file_name(){
        let name = this.file.name
        if(this.file.name.length > 15 ){
          name = `${name.substring(0, 15)}...`
        } 
        return name
      },
      status(){
        let status = new Object
        status.is_error = this.upload.status == 'error' 
        status.is_success = this.upload.status == 'success'
        status.is_inprogress = this.upload.status == 'inprogress'
        return status 
      }
    },
    methods : {
      push(){
        let config = {
          onUploadProgress: progressEvent => {
            this.upload.percentage = Math.floor((progressEvent.loaded * 100) / progressEvent.total);
          }
        }
        var params = new FormData()
        params.append('resource', this.file, this.file.name)
        params.append('a', this.api.action)
        params.append('uid', this.courseid)
        params.append('cid', this.authorid)
        Axios.post(this.api.route, params, config)
        .then( response => {
          if(response.status == 200 && response.data.valid){
            console.log(response.data)
            this.resource.id = response.data.data.resource.id
            this.resource.name = response.data.data.resource.name
            this.resource.path = response.data.data.resource.path
            this.resource.type = response.data.data.resource.type
            this.upload.status = 'success'
            // console.log("this.resource",this.resource)
          }else{
            this.upload.status = 'error'
          }
        })
        .catch(e => {
          this.upload.status = 'error'
        })
        .finally(()=> {
          // console.log("ALL END :V")
        })
      },
      local_url(){
        let url = URL.createObjectURL(this.file)
        return url
      },
      uploading(){
        let uploading = this.upload.status == 'inprogress'
        // console.log(uploading)
        return uploading
      },
      get_title(){
        let title = 'Status not found'
        let status = this.upload.status
        let label_for_status = typeof(this.label.status[this.upload.status]) != 'undefined'
        if(label_for_status){
          title = this.label.status[this.upload.status]
        }
        return title
      },
      get_size(){
        let size = 0
        let unit = ''
        byte = this.file.size
        if(byte > 0){
          let kilobyte = this.file.size / 1024
          size = kilobyte 
          unit = 'KB'
        }
        if(size >= 1024){
          let megabyte = size / 1024
          size = megabyte 
          unit = 'MB'
        }
        size = `(${size.toFixed(1)} ${unit})`
        return size
      },
      notify(){
        this.$emit('file.uploaded', 'subidito papetoooo')
      },
      is_image(){
        let is_image = this.file_type() == 'image'
        return is_image
      },
      file_type(){
        return 'image'
      },
      progress_color(){
        let red = '#f44336'
        let gray = '#607d8b'
        let green = '#8bc34a'
        var color = red
        if(this.status.is_inprogress){
          color = gray 
        }
        if(this.status.is_success){
          color = green 
        }
        return color
      },
      remove(){

      }

    }
  }
  return uploadfile
});