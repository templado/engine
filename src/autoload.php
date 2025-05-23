<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'templado\\engine\\csrfprotection' => '/csrfprotection/CSRFProtection.php',
                'templado\\engine\\csrfprotectionrenderer' => '/csrfprotection/CSRFProtectionRenderer.php',
                'templado\\engine\\csrfprotectionrendererexception' => '/csrfprotection/CSRFProtectionRendererException.php',
                'templado\\engine\\cssselector' => '/selectors/CSSSelector.php',
                'templado\\engine\\document' => '/document/Document.php',
                'templado\\engine\\documentcollection' => '/document/DocumentCollection.php',
                'templado\\engine\\documentexception' => '/document/DocumentException.php',
                'templado\\engine\\emptyelementsfilter' => '/serializer/EmptyElementsFilter.php',
                'templado\\engine\\emptyelementsfilterexception' => '/serializer/EmptyElementsFilterException.php',
                'templado\\engine\\exception' => '/Exception.php',
                'templado\\engine\\filter' => '/serializer/Filter.php',
                'templado\\engine\\formdata' => '/formdata/FormData.php',
                'templado\\engine\\formdataexception' => '/formdata/FormDataException.php',
                'templado\\engine\\formdatarenderer' => '/formdata/FormDataRenderer.php',
                'templado\\engine\\formdatarendererexception' => '/formdata/FormDataRendererException.php',
                'templado\\engine\\htmlserializer' => '/serializer/HTMLSerializer.php',
                'templado\\engine\\id' => '/Id.php',
                'templado\\engine\\ignore' => '/viewmodel/Ignore.php',
                'templado\\engine\\mergelist' => '/merger/MergeList.php',
                'templado\\engine\\mergelistexception' => '/merger/MergeListException.php',
                'templado\\engine\\merger' => '/merger/Merger.php',
                'templado\\engine\\mergerexception' => '/merger/MergerException.php',
                'templado\\engine\\notdefined' => '/viewmodel/NotDefined.php',
                'templado\\engine\\parsingexception' => '/document/ParsingException.php',
                'templado\\engine\\remove' => '/viewmodel/Remove.php',
                'templado\\engine\\selection' => '/selectors/Selection.php',
                'templado\\engine\\selector' => '/selectors/Selector.php',
                'templado\\engine\\serializer' => '/serializer/Serializer.php',
                'templado\\engine\\signal' => '/viewmodel/Signal.php',
                'templado\\engine\\staticnodelist' => '/StaticNodeList.php',
                'templado\\engine\\stringcollection' => '/viewmodel/StringCollection.php',
                'templado\\engine\\striprdfaattributestransformation' => '/transformation/StripRDFaAttributesTransformation.php',
                'templado\\engine\\transformation' => '/transformation/Transformation.php',
                'templado\\engine\\transformationprocessor' => '/transformation/TransformationProcessor.php',
                'templado\\engine\\viewmodelrenderer' => '/viewmodel/ViewModelRenderer.php',
                'templado\\engine\\viewmodelrendererexception' => '/viewmodel/ViewModelRendererException.php',
                'templado\\engine\\xmlheaderfilter' => '/serializer/XMLHeaderFilter.php',
                'templado\\engine\\xpathselector' => '/selectors/XPathSelector.php',
                'templado\\engine\\xpathselectorexception' => '/selectors/XPathSelectorException.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    },
    true,
    true
);
// @codeCoverageIgnoreEnd
