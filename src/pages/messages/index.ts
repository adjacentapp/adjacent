import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { MessagesProvider, Message, Conversation } from '../../providers/messages/messages';
import { AuthProvider } from '../../providers/auth/auth';
import { ShowMessagePage } from './show';
import { ProfilePage } from '../../pages/profile/profile';

// @IonicPage()
@Component({
  selector: 'page-message',
  templateUrl: 'index.html',
})
export class MessagesPage {
  loading: boolean;
  items: any[];
  filters: any = [];
  reachedEnd: boolean = false;

  constructor(public navCtrl: NavController, public navParams: NavParams, private msg: MessagesProvider, private auth: AuthProvider) {
  	this.loading = true;
  	this.msg.getConversations(this.auth.currentUser.id)
  		.subscribe(
  			items => this.items = items,
  			error => console.log(<any>error),
  			() => this.loading = false
  		);
  }

  itemTapped(event, item) {
  	this.navCtrl.push(ShowMessagePage, {
  		item: item
  	});
  }

  goToProfile(event, user_id) {
    event.stopPropagation();
    this.navCtrl.push(ProfilePage, {
      user_id: user_id
    });
  }

  doInfinite(e){
    // let offset = this.items.length;
    // this.bookmarks.getBookmarks(this.auth.currentUser.id, offset, this.filters)
    // 	.subscribe(
    // 		items => {
    // 			this.items = this.items.concat(items);
    // 			if(!items.length){
    // 				this.reachedEnd = true;
    // 			}
    // 		},
    // 		error => console.log(<any>error),
    // 		() => {
    // 			this.loading = false;
    			e.complete();
    // 		}
    // 	);
  }

  

}