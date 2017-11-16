import { Component } from '@angular/core';
import { NavController, ViewController, NavParams } from 'ionic-angular';
import { JoinNetworkPage } from '../network/join';
import * as globs from '../../app/globals'

@Component({
  selector: 'page-filter',
  templateUrl: 'filter.html',
})
export class FilterPage {
  public changed = false;
  public networks = globs.NETWORKS;
  public industries = ['Any', ...globs.INDUSTRIES];
  public selected_industry = "Any";
  public selected_network_ids = this.networks.map(item => item.id);

  constructor(public navCtrl: NavController, public viewController: ViewController, public navParams: NavParams) {
   console.log(this.navParams.data);
   this.selected_industry = this.navParams.data.industry || this.selected_industry;
   this.selected_network_ids = this.navParams.data.network_ids || this.selected_network_ids
 }

  onChange(e){
    this.changed = true;
  }

  apply(){
    this.viewController.dismiss({
      changed: this.changed,
      industry: this.selected_industry,
      network_ids: this.selected_network_ids
    });
  }

  joinNetwork(e){
    this.navCtrl.push(JoinNetworkPage);
  }

}
