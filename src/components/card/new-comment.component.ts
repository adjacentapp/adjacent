import { Component, Input } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';

@Component({
  selector: 'new-comment-component',
  templateUrl: 'new-comment-component.html'
})
export class NewCommentComponent {
  @Input() card_id: string;
  item: {
    timestamp: any,
    message: string,
    likes?: number, 
    user: {
      fir_name: string, 
      las_name: string, 
      photo_url: string
    }
  };

  constructor(public navCtrl: NavController, public navParams: NavParams) {
    this.item = {
      timestamp: 'now',
      message: '',
      user: {
        fir_name: 'Your',
        las_name: 'Name',
        photo_url: 'https://graph.facebook.com/10154226234631397/picture?type=large'
      }
    }
  }

  postComment(event, user_id) {
  	event.stopPropagation();
    alert('send message');
  }

}
