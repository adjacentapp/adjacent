import { Component, Input, Output, EventEmitter } from '@angular/core';
import { NavController, NavParams } from 'ionic-angular';
import { AuthProvider } from '../../providers/auth/auth';
import { WallProvider } from '../../providers/wall/wall';

@Component({
  selector: 'new-comment-component',
  templateUrl: 'new-comment-component.html'
})
export class NewCommentComponent {
  @Input() card_id: string;
  @Input() handlePrependComment: any;
  @Output() newComment: EventEmitter<any> = new EventEmitter();

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

  constructor(public navCtrl: NavController, public navParams: NavParams, private auth: AuthProvider) {
    this.item = {
      timestamp: 'now',
      message: '',
      user: this.auth.currentUser,
    }
  }

  postComment(e, item) {
    e.stopPropagation();

    let data = {
      user: item.user,
      message: item.message
    };
    // this.wall.postComment(data).subscribe(
    //   success => console.log(success),
    //   error => console.log(error)
    // );

    this.newComment.emit(data);
    this.item.message = '';
  }

}
