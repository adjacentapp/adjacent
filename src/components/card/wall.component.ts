import { Component, Input } from '@angular/core';
import { Http } from '@angular/http'
import 'rxjs/add/operator/map';
import { WallService } from '../../services/wall.service';

@Component({
  selector: 'wall-component',
  templateUrl: 'wall-component.html'
})
export class WallComponent {
  @Input() card_id: string;
  comments: any[];
  loading: boolean;

  constructor(public http: Http, private wallService: WallService) {}

  ngOnInit() {
    this.loading = true;
    this.wallService.getWall(this.card_id)
      .subscribe(
        comments => this.comments = comments,
        error => console.log(error),
        () => this.loading = false
      );
  }

  doInfinite(e){
    console.log(e);
    setTimeout(function(){
      e.complete();
    }, 1000);
    // this.service.getCard()
    // .subscribe(
    //   data => this.comments.push(...data.results),
    //   err => console.log(err),
    //   () => e.complete()
    // );
  }
}
