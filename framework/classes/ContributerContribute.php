<?php

class ContributerContribute {
	
	
    public function contributer_contribute() {

        if ( is_user_logged_in() ) {
            return $this->render_contributer_contribute();
        }
        else {
            $contributer_login_rendered = new ContributerLogin();
            return $contributer_login_rendered->render_contributer_login();
        }

    }
	
	
    public function render_contributer_contribute() {

            ob_start();
            ?>
            
            <form class="contributer-editor">
                
                <p>
                  <label for="title">Title</label>
                  <input required="required" id="title" type="text"/>
                </p>
                <p><span>Post Type</span>
                  <input id="standard" type="radio" name="post-type" value="Standard"/>
                  <label for="standard">Standard</label>
                  <input id="image" type="radio" name="post-type" value="Image"/>
                  <label for="image">Image</label>
                  <input id="video" type="radio" name="post-type" value="Video"/>
                  <label for="video">Video</label>
                  <input id="gallery" type="radio" name="post-type" value="Gallery"/>
                  <label for="gallery">Gallery</label>
                </p>
                <div id="feat-img-field" class="field"><span>featured image</span>
                  <div class="contributer-upload"> 
                    <p>drag 'n' drop <br/>
                      <input type="button" value="find files" class="files"/>
                    </p>
                  </div>
                </div>
                <div id="gallery-field" class="field"><span>gallery images</span>
                  <div class="contributer-upload"> 
                    <p>drag 'n' drop <br/>
                      <input type="button" value="find files" class="files"/>
                    </p>
                  </div>
                </div>
                <p id="video-field" class="field">
                  <label for="vid-url">Video URL</label>
                  <input id="vid-url" type="text"/>
                </p>
                <p>
                  <label for="content">Content</label>
                  <textarea required="required" id="content" type="text"></textarea>
                </p>
                <p>
                  <label for="tags">Tags</label>
                  <input id="description" type="text"/>
                </p>
                
                <p>
                    <span>Category</span>
                    <select>
                        <option value="" disabled="disabled" selected="selected">Choose your Category</option>
                        <option value="1">Category 1</option>
                        <option value="2">Category 2</option>
                        <option value="3">Category 3</option>
                    </select>
                </p>
                
                <input type="submit" value="Save"/>
                
              </form>
              <!-- form editor end -->


            <?php
            $html_output = ob_get_clean();
            return $html_output;

    }

	
}
