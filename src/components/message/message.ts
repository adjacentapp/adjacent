import { Component, Input } from '@angular/core';
import { NavController } from 'ionic-angular';
import { AuthProvider, User } from '../../providers/auth/auth';
import { ProfilePage } from '../../pages/profile/profile';

@Component({
  selector: 'message',
  templateUrl: 'message.html'
})
export class MessageComponent {
  // item: {user: User, text: string};
  @Input() item: any;
  other: boolean;

  constructor(public navCtrl: NavController, private auth: AuthProvider) {}

  ngOnInit() {
  	this.other = this.item.user.id !== this.auth.currentUser.id;
  }

  goToProfile(event, user_id) {
  	event.stopPropagation();
  	this.navCtrl.push(ProfilePage, {
  		user_id: user_id
  	});
  }
}
