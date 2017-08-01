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
  // @Input() handlePrependComment: any;
  @Output() newComment: EventEmitter<any> = new EventEmitter();

  item: {
    message: string,
    card_id: any,
    user: {
      fir_name: string,
      las_name: string,
      photo_url: string
    }
  };

  constructor(public navCtrl: NavController, public navParams: NavParams, private auth: AuthProvider, private wall: WallProvider) {
    this.item = {
      message: '',
      card_id: this.card_id,
      user: this.auth.currentUser,
    }
  }

  postComment(e, item) {
    e.stopPropagation();

    let data = {
      message: item.message,
      card_id: this.card_id,
      user: item.user
    };
    this.wall.postComment(data).subscribe(
      success => {
        this.newComment.emit(success);
        this.item.message = '';
      },
      error => console.log(error)
    );
  }

}
