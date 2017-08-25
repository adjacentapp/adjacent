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
  @Input() founder_id: number;
  @Input() response_to: string = null;
  @Output() newComment: EventEmitter<any> = new EventEmitter();
  founder: boolean = false;
  loading: boolean = false;
  item: {
    message: string,
    card_id: any,
    response_to: string,
    user: {
      fir_name: string,
      las_name: string,
      photo_url: string
    }
  };

  constructor(public navCtrl: NavController, public navParams: NavParams, private auth: AuthProvider, private wall: WallProvider) {}

  ngOnInit(){
    this.founder = this.founder_id == this.auth.currentUser.id;
    this.item = {
      message: '',
      card_id: this.card_id,
      user: this.auth.currentUser,
      response_to: this.response_to
    }
    console.log(this.item);
  }

  postComment(e, item) {
    e.stopPropagation();
    this.loading = true;
    
    let data = {...this.item};

    this.wall.postComment(data).subscribe(
      success => {
        this.newComment.emit(success);
        this.item.message = '';
        this.loading = false;
      },
      error => console.log(error)
    );
    // risky lazing
    // this.newComment.emit(data);
  }

}
