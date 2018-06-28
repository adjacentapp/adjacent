import { Component } from '@angular/core';
import { NavController, ViewController, NavParams } from 'ionic-angular';
import * as globs from '../../app/globals'

@Component({
  selector: 'page-ftue-filter',
  templateUrl: 'ftue-filter.html',
})
export class FtueFilterPage {
  constructor(public navCtrl: NavController, public viewController: ViewController, public navParams: NavParams) {
   
 }

  close(){
    this.viewController.dismiss();
  }

}
