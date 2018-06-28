import { Component, Input } from '@angular/core';
import { Platform, NavController, NavParams, ToastController, AlertController, ActionSheetController, LoadingController, Loading, ViewController } from 'ionic-angular';
import { Camera, CameraOptions } from '@ionic-native/camera';
import { File } from '@ionic-native/file';
// import { FileTransfer } from '@ionic-native/file-transfer';
import { FilePath } from '@ionic-native/file-path';
import 'rxjs/add/operator/map';
import { ProfileProvider, Profile } from '../../providers/profile/profile';
import { AuthProvider } from '../../providers/auth/auth';
import * as globs from '../../app/globals'

declare var cordova: any;

@Component({
	selector: 'edit-profile-page',
	templateUrl: 'edit.html'
})
export class EditProfilePage {
	loading: Loading;
	profile: Profile;
	skills = globs.SKILLS;
	updateCallback: any;
	photoUpload = {
		valid: true,
		msg: '',
		loading: false
	};
	lastImage: string = null;

  @Input() inputProfile: Profile;
  @Input() inputUser;

	constructor(
		public platform: Platform,
		public navCtrl: NavController, 
		public navParams: NavParams, 
		private profileProvider: ProfileProvider, 
		private auth: AuthProvider, 
		private loadingCtrl: LoadingController, 
		private alertCtrl: AlertController,
		private actionSheetCtrl: ActionSheetController,
		private toastCtrl: ToastController,
		private camera: Camera,
		// private transfer: FileTransfer, 
		private file: File, 
		private filePath: FilePath,
		private viewCtrl: ViewController
	) {
    console.log(navParams)
    // if(this.inputProfile)
    //   this.profile = this.inputProfile
    // else
    //   this.profile = {...navParams.get('profile')};
    
    // if(this.inputUser)
    //   this.profile.user = this.inputUser
    // else
    //   this.profile.user = {...navParams.get('profile').user}

    // this.updateCallback = navParams.get('updateCallback');
	}

  ngOnInit() {
    console.log(this.inputProfile)
    console.log(this.inputUser)
    
   if(this.inputProfile)
     this.profile = this.inputProfile
   
   if(this.inputUser)
     this.profile.user = this.inputUser
  }

	ionViewDidLoad() {
	  this.viewCtrl.showBackButton(false);
	}

	cancel(e){
    	e.preventDefault();
    	this.navCtrl.pop();
    }

	saveProfile() {
		this.showLoading();
		this.profileProvider.updateProfile(this.profile).subscribe(
			profile => {
				this.updateCallback(profile).then(()=>{
				  	this.navCtrl.pop();
				});
			},
			error => {
				this.showError(error);
			}
		);
	}

	public updateNamesByIds(){
		this.profile.skill_names = globs.SKILLS
	            .filter(item => this.profile.skill_ids.indexOf(item.id) >= 0)
	            .map(item => item.name);
	}

	tappedPhoto(e){
		e.preventDefault();
		let actionSheet = this.actionSheetCtrl.create({
			title: 'Select Image Source',
			buttons: [{
				text: 'Select from Library',
			  handler: () => { this.takePicture(this.camera.PictureSourceType.PHOTOLIBRARY); }
		  },{
		    text: 'Use Camera',
		    handler: () => { this.takePicture(this.camera.PictureSourceType.CAMERA); }
		  },{
        text: 'Cancel',
        role: 'cancel'
      }]
		});
		actionSheet.present();
	}

	takePicture(sourceType){
		let options: CameraOptions = {
		  quality: 50,
		  destinationType: this.camera.DestinationType.FILE_URI,
		  encodingType: this.camera.EncodingType.JPEG,
		  mediaType: this.camera.MediaType.PICTURE,
		  cameraDirection: this.camera.Direction.FRONT,
		  correctOrientation: true,
		  saveToPhotoAlbum: false,
		  sourceType: sourceType
		}

		this.camera.getPicture(options).then((imagePath) => {
			this.photoUpload.loading = true;
			// Special handling for Android library
			if (this.platform.is('android') && sourceType === this.camera.PictureSourceType.PHOTOLIBRARY) {
			  this.filePath.resolveNativePath(imagePath)
			    .then(filePath => {
			      let correctPath = filePath.substr(0, filePath.lastIndexOf('/') + 1);
			      let currentName = imagePath.substring(imagePath.lastIndexOf('/') + 1, imagePath.lastIndexOf('?'));
			      this.copyFileToLocalDir(correctPath, currentName, this.createFileName());
			    });
			} else {
			  var currentName = imagePath.substr(imagePath.lastIndexOf('/') + 1);
			  var correctPath = imagePath.substr(0, imagePath.lastIndexOf('/') + 1);
			  this.copyFileToLocalDir(correctPath, currentName, this.createFileName());
			}	            
		}, (err) => {
		 this.showError(err);
		});
	}

	private createFileName() {
	  var d = new Date(),
	  n = d.getTime(),
	  newFileName =  n + ".jpg";
	  return newFileName;
	}

	private copyFileToLocalDir(namePath, currentName, newFileName) {
	  this.file.copyFile(namePath, currentName, cordova.file.dataDirectory, newFileName).then(success => {
	    this.profileProvider.uploadPhoto(newFileName).then(
	    	data => {
	    		console.log('data', data);
	    		if(data.valid){
	    			this.profile.user.photo_url = data.photo_url;
						this.presentToast('Image successfully uploaded.');
					}
					else{
						this.presentToast(data.msg);
					}
					this.photoUpload.loading = false;
	    	},
	    	err => {
	    		console.log('error', err);
	    		this.photoUpload.loading = false;
	    	}
	    );
	  }, error => {
	    this.presentToast('Error while storing file.');
	    this.photoUpload.loading = false;
	  });
	}
	 
	private presentToast(text) {
	  let toast = this.toastCtrl.create({
	    message: text,
	    duration: 2500,
	    position: 'top'
	  });
	  toast.present();
	}

	showLoading() {
	  this.loading = this.loadingCtrl.create({
	    content: 'Please wait...',
	    dismissOnPageChange: true
	  });
	  this.loading.present();
	}

	showError(text) {
	  console.log(text);
	  if(this.loading)
	    this.loading.dismiss();

	  let alert = this.alertCtrl.create({
	    title: 'Oh no!',
	    // subTitle: text,
	    subTitle: "Something went wrong. Please try again.",
	    buttons: ['OK']
	  });
	  alert.present(prompt);
	}
}
