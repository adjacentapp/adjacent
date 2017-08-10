import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams } from 'ionic-angular';
import { AuthProvider, User } from '../../providers/auth/auth';
import { ProfilePage } from '../../pages/profile/profile';

@IonicPage()
@Component({
  selector: 'page-followers',
  templateUrl: 'followers.html',
})
export class FollowersPage {
  card_id: number;
  items: User[];
  loading: boolean;
  reachedEnd: boolean = false;
  limit: number = 20;

  constructor(public navCtrl: NavController, public navParams: NavParams, private auth: AuthProvider) {
  	this.loading = true;
  	this.card_id = navParams.get('card_id');
  	this.auth.getFollowers(this.card_id, 0, this.limit)
  		.subscribe(
  			items => {
  				this.items = items;
  				this.reachedEnd = items.length < this.limit;
  			},
  			error => console.log(<any>error),
  			() => this.loading = false
  		);
  }

  goToProfile(e, item){
    event.stopPropagation();
    this.navCtrl.push(ProfilePage, {
      user_id: item.id
    });
  }

  doInfinite (): Promise<any> {
    return new Promise((resolve) => {
  	  setTimeout(() => {
  	    let offset = this.items.length;
  	    this.auth.getFollowers(this.card_id, offset, this.limit)
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
