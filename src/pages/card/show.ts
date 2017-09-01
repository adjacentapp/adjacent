import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { CardProvider } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';
import { NewCardPage } from '../../pages/card/new';
import { ShowMessagePage } from '../../pages/messages/show';
import { Conversation } from '../../providers/messages/messages';

@Component({
  selector: 'show-card-page',
  templateUrl: 'show.html'
})
export class ShowCardPage {
  item: any;
  founder: boolean = false;
  deleteCallback: any;
  updateCallback: any;
  loading: boolean = false;
  noEdit: boolean = false;

  constructor(public navCtrl: NavController, public navParams: NavParams, private card: CardProvider, private auth: AuthProvider) {
    this.deleteCallback = this.navParams.get('deleteCallback');
    this.updateCallback = this.navParams.get('updateCallback');
    if(navParams.get('item')){
      this.item = navParams.get('item');
      this.founder = this.item.founder_id == this.auth.currentUser.id;
    }
    else if (navParams.get('card_id')){
      this.noEdit = true;
      this.loading = true;
      this.item = this.card.getCardById(navParams.get('card_id'), this.auth.currentUser.id).subscribe(
        item => {
          this.item = item;
          this.founder = this.item.founder_id == this.auth.currentUser.id;
        },
        error => {
          console.log('trying to get_card_by_id again', error);
          // this.getDeck();
        },
        () => this.loading = false
      );
    }
  }

  editTapped = () => {
    let handleDelete = (_params) => {
      return new Promise((resolve, reject) => {
        this.deleteCallback(_params).then(()=>{
          resolve();
         });
      });
    }
    let handleUpdate = (_params) => {
      return new Promise((resolve, reject) => {
        this.item = {..._params};
        if(this.updateCallback)
          this.updateCallback(_params).then(()=>{
            resolve();
          })
        else
          resolve();
      });
    }
    this.navCtrl.push(NewCardPage, {
      item: this.item,
      deleteCallback: handleDelete,
      updateCallback: handleUpdate
    });
  }

  goToMessage(){
    this.navCtrl.push(ShowMessagePage, {
      item: new Conversation(
        null, // id
        {id: this.item.founder_id}, // other
        {...this.item}, // card
        [], // messages
        null, // timstamp
        null // unread
      )
    });
  }

}
