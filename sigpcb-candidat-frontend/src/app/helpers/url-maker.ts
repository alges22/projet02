export class UrlMaker {
  protected query: { kay: string, value: any }[] = []

  constructor(private url: string) {

  }

  addQuery(key: string, value: any) {
    this.query.push({
      kay: key,
      value: value
    })
  }

  generateUrl() {
    const q = []
    for (let i = 0; i < this.query.length; i++) {
      const element = this.query[i];
      q.push(`${element.kay}=${element.value}`)
    }
    if (q.length > 0) {
      let queryString = q.join("&")
      if (this.url.includes('?')) {
        this.url =this.url.concat(`&${queryString}`)
      } else {
        this.url =this.url.concat(`?${queryString}`)
      }
    }

    return this.url
  }
}
