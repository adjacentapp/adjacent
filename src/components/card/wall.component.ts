import { Component, Input } from '@angular/core';
import { Http } from '@angular/http'
import 'rxjs/add/operator/map';
import { WallProvider } from '../../providers/wall/wall';

@Component({
  selector: 'wall-component',
  templateUrl: 'wall-component.html'
})
export class WallComponent {
  @Input() card_id: string;
  @Input() founder_id: number;
  items: any[] = [];
  loading: boolean;
  reachedEnd: boolean = false;
  limit: number = 10;

  constructor(public http: Http, private wall: WallProvider) {}

  ngOnInit() {
    this.loading = true;
    this.wall.getWall(this.card_id, 0, this.limit)
      .subscribe(
        items => {
          this.items = items;
          this.reachedEnd = items.length < this.limit;
        },
        error => console.log(error),
        () => this.loading = false
      );
  }

  prependComment(item){
    this.items.unshift(item);
    // after this, the offset of doInfinite will be wrong...
  }

  doRefresh (e) {
    this.loading = true;
    this.wall.getWall(this.card_id, 0, this.limit)
      .subscribe(
        items => {
          this.items = items;
          this.reachedEnd = items.length < this.limit;
        },
        error => console.log(<any>error),
        () => {
          this.loading = false;
          e.complete();
        }
      );
  }

  doInfinite (): Promise<any> {
    return new Promise((resolve) => {
      setTimeout(() => {
        let offset = this.items.length;
        this.wall.getWall(this.card_id, offset, this.limit)
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
