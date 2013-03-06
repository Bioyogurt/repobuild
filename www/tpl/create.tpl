<form action="/takecreate.php" method="POST" class="form-horizontal">
    <div class="control-group">
        <label class="control-label" for="inputTitle">Name</label>
        <div class="controls">
            <input type="text" id="inputTitle" placeholder="Title" name="title" class="span3">
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="inputOs">Os</label>
        <div class="controls">
            <select name="os" class="span3" id="inputOs">
                {%os%}
            </select>
        </div>
    </div>
    <div class="control-group">
        <label class="control-label" for="inputArch">Arch</label>
        <div class="controls">
            <select name="arch" class="span3" id="inputArch">
                {%arch%}
            </select>
        </div>
    </div>

    <div class="control-group">
        <div class="controls">
            <button type="submit" class="btn btn-primary">Create</button>
            <a href="/repos.php" class="btn">Cancel</a>
        </div>
    </div>
</form>