import { Component, Input } from '@angular/core';
import { NavController } from 'ionic-angular';
import { AuthProvider, User } from '../../providers/auth/auth';
import { ProfilePage } from '../../pages/profile/profile';
import { ShowCardPage } from '../../pages/card/show';

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

  avatarTapped(event, item){
    event.stopPropagation();
    
    if(item.user)
      this.navCtrl.push(ProfilePage, {
        user_id: item.user.id
      });
    else if(item.card)
      this.navCtrl.push(ShowCardPage, {
        item: item.card
      });
  }
}
