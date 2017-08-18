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
  @Input() item: any;
  @Input() founder_id: number;
  founder: boolean = false;
  other: boolean;

  constructor(public navCtrl: NavController, private auth: AuthProvider) {}

  ngOnInit() {
  	this.other = this.item.user.id !== this.auth.currentUser.id;
    this.founder = this.item.user.id  === this.founder_id
  }

  avatarTapped(event, item){
    event.stopPropagation();
    
    // if(item.user)
    //   this.navCtrl.push(ProfilePage, {
    //     user_id: item.user.id
    //   });
    // else if(item.founder)
    //   this.navCtrl.push(ShowCardPage, {
    //     item: item.card
    //   });
    //////// ((this.card)) does not exist!
  }
}
