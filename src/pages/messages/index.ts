import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { MessagesProvider } from '../../providers/messages/messages';
import { AuthProvider } from '../../providers/auth/auth';
import { ShowMessagePage } from './show';
import { ProfilePage } from '../../pages/profile/profile';
import { ShowCardPage } from '../../pages/card/show';
import { Badge } from '@ionic-native/badge';

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
  limit: number = 10;

  constructor(public navCtrl: NavController, public navParams: NavParams, public msg: MessagesProvider, private auth: AuthProvider, private badge: Badge) {
  	this.loading = true;
    this.msg.getConversations(this.auth.currentUser.id, null, null, 0, this.limit)
  		.subscribe(
  			items => {
          this.items = items;
          this.reachedEnd = items.length < this.limit;
        },
  			error => console.log(<any>error),
  			() => this.loading = false
  		);
  }

  itemTapped(event, item) {
    if(item.unread){
      this.msg.markAsRead(this.auth.currentUser.id, item.id).subscribe(
          items => console.log('deleted: ', items),
          error => console.log(<any>error)
      );
      item.unread = false;
      this.badge.decrease(1);
      this.msg.unread_ids = this.msg.unread_ids.filter(convo_id => convo_id !== item.id);
    }
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

  goToCard(event, item) {
    event.stopPropagation();
    this.navCtrl.push(ShowCardPage, {
      card_id: item.id
    });
  }

  doRefresh (e) {
    this.msg.getConversations(this.auth.currentUser.id)
      .subscribe(
        items => this.items = items,
        error => console.log(<any>error),
        () => {
          e.complete();
        }
      );
  }

  doInfinite (): Promise<any> {
    return new Promise((resolve) => {
      setTimeout(() => {
        let offset = this.items.length;
        this.msg.getConversations(this.auth.currentUser.id, null, null, offset, this.limit)
          .subscribe(
          items => {
            this.items = this.items.concat(items);
            this.reachedEnd = items.length < this.limit;
            resolve();
          },
          error => console.log(<any>error)
        );
      }, 500);
    });
  }

}
