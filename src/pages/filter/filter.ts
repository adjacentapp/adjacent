import { Component } from '@angular/core';
import { ViewController, NavParams } from 'ionic-angular';
import * as globs from '../../app/globals'

@Component({
  selector: 'page-filter',
  templateUrl: 'filter.html',
})
export class FilterPage {
  public changed = false;
  public networks = globs.NETWORKS;
  public industries = globs.INDUSTRIES;
  public selected_industry;
  public selected_network_ids = [0];

  constructor(public viewController: ViewController, public navParams: NavParams) {
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

}
