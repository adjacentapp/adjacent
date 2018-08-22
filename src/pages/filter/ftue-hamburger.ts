import { Component } from '@angular/core';
import { NavController, ViewController, NavParams } from 'ionic-angular';
import * as globs from '../../app/globals'

@Component({
  selector: 'page-ftue-hamburger',
  templateUrl: 'ftue-hamburger.html',
})
export class FtueHamburgerPage {
  constructor(public navCtrl: NavController, public viewController: ViewController, public navParams: NavParams) {
   
 }

  close(){
    this.viewController.dismiss();
  }

}
