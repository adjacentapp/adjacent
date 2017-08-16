import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { CardProvider } from '../../providers/card/card';
import { AuthProvider } from '../../providers/auth/auth';
import { NewCardPage } from '../../pages/card/new';
import { ShowMessagePage } from '../../pages/messages/show';

@Component({
  selector: 'show-card-page',
  templateUrl: 'show.html'
})
export class ShowCardPage {
  item: any;
  founder: boolean = false;
  deleteCallback: any;
  updateCallback: any;

  constructor(public navCtrl: NavController, public navParams: NavParams, private card: CardProvider, private auth: AuthProvider) {
    this.deleteCallback = this.navParams.get('deleteCallback');
    this.updateCallback = this.navParams.get('updateCallback');
    this.item = navParams.get('item');
    if(this.item.founder_id == this.auth.currentUser.id) this.founder = true;
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
      item: {
        id: null,
        card_id: this.item.id,
        messages: [],
        other: {
          id: this.item.founder_id
        }
      }
    }); 
  }

}
