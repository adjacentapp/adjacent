import { Component, Input } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { ProfilePage } from '../../pages/profile/profile';

@Component({
  selector: 'comment-component',
  templateUrl: 'comment-component.html'
})
export class CommentComponent {
  @Input() item: {
    timestamp: any,
    message: string,
    likes?: number, 
  	user: {
      fir_name: string, 
      las_name: string, 
      photo_url: string
    }
  };

  constructor(public navCtrl: NavController, public navParams: NavParams) {}

  goToProfile(event, user_id) {
  	event.stopPropagation();
  	this.navCtrl.push(ProfilePage, {
  		user_id: user_id
  	});
  }

}
