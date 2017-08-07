import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';

// @IonicPage()
@Component({
  selector: 'show-page-message',
  templateUrl: 'show.html',
})
export class ShowMessagePage {
  item: any;

  constructor(public navCtrl: NavController, public navParams: NavParams) {
  	this.item = navParams.get('item');
  }

}
