import { Component } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { CardProvider } from '../../providers/card/card';

@Component({
  selector: 'show-card-page',
  templateUrl: 'show.html'
})
export class ShowCardPage {
  item: any;
  founder: boolean = false;

  constructor(public navCtrl: NavController, public navParams: NavParams, private card: CardProvider) {
    // If we navigated to this page, we will have an item available as a nav param
    this.item = navParams.get('item');
    if(this.item.founder_id == 1) this.founder = true;
  }

  iterateTapped(){
    alert("Iterate on your idea.")
  }

  doFollow (e, item){
    e.stopPropagation();
    let data = {
      user_id: 1,
      card_id: item.id,
      new_entry: !item.following
    };
    this.card.follow(data).subscribe(
      success => console.log(success),
      error => console.log(error)
    );
    // dangerous optimism!
    item.following = !item.following;
  }

  doRefresh (e) {
  	setTimeout(function(){
  		e.complete();
  	}, 1000);
  	// this.service.getCard()
  	// .subscribe(
  	// 	data => this.item = data.results,
  	// 	err => console.log(err),
  	// 	() => e.complete()
  	// );
  }
}
